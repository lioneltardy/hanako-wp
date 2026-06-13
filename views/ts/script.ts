import '@tailwindplus/elements';
import { Analytics } from '../../views/ts/components/Analytics';
import { CookiesConsent } from '../../views/twig/components/CookiesConsent';
import { Carousel } from '../../views/twig/components/Carousel';
import { Collapse } from '../../views/twig/components/Collapse';
import { Dropdown } from '../../views/twig/components/Dropdown';
import { LazyLoader } from '../../views/ts/components/LazyLoader';
import { ScrollSpy } from '../../views/ts/components/ScrollSpy';
import { DarkMode } from '../../views/twig/components/DarkMode';
import { Menu } from '../../views/twig/components/Menu';
import { Demo } from '../../views/twig/modules/demo';

(new Analytics()).init();
(new Carousel()).init();
(new Collapse()).init();
(new Demo()).init();
(new Dropdown()).init();
(new LazyLoader()).init();
(new ScrollSpy()).init();
(new DarkMode()).init();
(new Menu()).init();

// Always at the end
(new CookiesConsent()).init();
