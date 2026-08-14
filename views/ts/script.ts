async function loadTailwindPlusElementsIfNeeded(): Promise<void> {
  const hasTailwindPlusElements = Array.from(document.getElementsByTagName('*')).some(
    (element: Element) => element.localName.startsWith('el-'),
  );

  if (!hasTailwindPlusElements) return;

  await import('@tailwindplus/elements');
}

import { Analytics } from '../../views/ts/components/Analytics';
import { Consent } from '../twig/components/Consent';
import { Carousel } from '../../views/twig/components/Carousel';
import { Collapse } from '../../views/twig/components/Collapse';
import { LazyLoader } from '../twig/components/LazyLoader';
import { ScrollSpy } from '../../views/ts/components/ScrollSpy';
import { Menu } from '../../views/twig/components/Menu';
import { Embed } from '../../views/twig/components/Embed';
import { Ui } from '../twig/components/UI';

//void loadTailwindPlusElementsIfNeeded(); // Uncomment this line to load Tailwind Plus Elements

(new Analytics()).init();
(new Carousel()).init();
(new Collapse()).init();
(new Embed()).init();
(new LazyLoader()).init();
(new ScrollSpy()).init();
(new Ui()).init();
(new Menu()).init();

// Always at the end
(new Consent()).init();
