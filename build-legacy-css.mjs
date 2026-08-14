import fs from 'node:fs';
import path from 'node:path';
import postcss from 'postcss';

const rootDir = process.cwd();
const distDir = path.join(rootDir, 'dist');
const manifestPath = path.join(distDir, '.vite', 'manifest.json');
const legacyOutputPath = path.join(distDir, 'css', 'style-legacy.css');
const legacyOverridesPath = path.join(rootDir, 'views', 'css', 'legacy', 'style.css');

function readManifest() {
  if (!fs.existsSync(manifestPath)) {
    throw new Error(`Manifest not found: ${manifestPath}`);
  }

  const raw = fs.readFileSync(manifestPath, 'utf8');
  const parsed = JSON.parse(raw);

  if (!parsed['views/css/style.css']?.file) {
    throw new Error('Manifest entry "views/css/style.css" not found.');
  }

  return parsed;
}

function flattenLayers(css) {
  const root = postcss.parse(css);

  root.walkAtRules('layer', (rule) => {
    if (rule.nodes && rule.nodes.length > 0) {
      rule.replaceWith(...rule.nodes);
      return;
    }

    rule.remove();
  });

  root.walkAtRules('import', (rule) => {
    if (rule.params.includes('layer(')) {
      rule.params = rule.params.replace(/\s*layer\([^)]*\)/g, '').trim();
    }
  });

  return root.toString();
}

function splitCssValues(value) {
  const values = [];
  let depth = 0;
  let currentValue = '';

  for (const character of value.trim()) {
    if (character === '(') depth += 1;
    if (character === ')') depth -= 1;

    if (/\s/.test(character) && depth === 0) {
      if (currentValue) {
        values.push(currentValue);
        currentValue = '';
      }
      continue;
    }

    currentValue += character;
  }

  if (currentValue) values.push(currentValue);

  return values;
}

function transformLogicalPadding(css) {
  const root = postcss.parse(css);

  root.walkDecls(/^-?padding-(block|inline)$/, (declaration) => {
    const [firstValue, secondValue = firstValue] = splitCssValues(declaration.value);
    const properties = declaration.prop.endsWith('block')
      ? ['padding-top', 'padding-bottom']
      : ['padding-left', 'padding-right'];

    declaration.cloneBefore({ prop: properties[0], value: firstValue });
    declaration.cloneBefore({ prop: properties[1], value: secondValue });
    declaration.remove();
  });

  return root.toString();
}

function readLegacyOverrides() {
  if (!fs.existsSync(legacyOverridesPath)) return '';

  const overrides = fs.readFileSync(legacyOverridesPath, 'utf8').trim();
  if (overrides.length === 0) return '';

  // Keep overrides resilient if @layer is used accidentally in this file.
  return flattenLayers(overrides);
}

function main() {
  const manifest = readManifest();
  const modernCssRelPath = manifest['views/css/style.css'].file;
  const modernCssPath = path.join(distDir, modernCssRelPath);

  if (!fs.existsSync(modernCssPath)) {
    throw new Error(`Built CSS not found: ${modernCssPath}`);
  }

  const modernCss = fs.readFileSync(modernCssPath, 'utf8');
  const legacyCss = transformLogicalPadding(flattenLayers(modernCss));
  const legacyOverrides = readLegacyOverrides();
  const finalLegacyCss = legacyOverrides
    ? `${legacyCss}\n\n/* Legacy custom overrides */\n${legacyOverrides}\n`
    : legacyCss;

  fs.mkdirSync(path.dirname(legacyOutputPath), { recursive: true });
  fs.writeFileSync(legacyOutputPath, finalLegacyCss, 'utf8');

  process.stdout.write(`Legacy CSS written to ${legacyOutputPath}\n`);
}

main();
