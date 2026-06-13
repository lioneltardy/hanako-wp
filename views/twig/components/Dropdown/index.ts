import { Component } from 'hanako-ts/dist-legacy/Component';

export class Dropdown extends Component {
  constructor(isDebugEnabled: boolean = false) {
    super('Dropdown', false);
  }

  public async init(): Promise<void> {
    await super.init();

    const toggles = Array.from(document.querySelectorAll<HTMLElement>('[data-ui-dropdown-toggle]'));

    toggles.forEach((toggle) => {
      const menu = this.getMenu(toggle);
      if (!menu) return;

      toggle.setAttribute('aria-expanded', 'false');
      menu.setAttribute('role', 'menu');
      menu.setAttribute('hidden', '');

      toggle.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        const shouldOpen = toggle.getAttribute('aria-expanded') !== 'true';

        this.closeAll();
        if (shouldOpen) {
          toggle.setAttribute('aria-expanded', 'true');
          menu.removeAttribute('hidden');
          menu.classList.add('is-open');
        }
      });

      menu.addEventListener('click', (event) => {
        const target = event.target as HTMLElement;
        if (target.closest('[data-hw-dropdown-close]')) {
          this.close(toggle);
        }
      });
    });

    document.addEventListener('click', () => this.closeAll());
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') this.closeAll();
    });

    this.success();
  }

  private closeAll(): void {
    const toggles = Array.from(document.querySelectorAll<HTMLElement>('[data-hw-dropdown-toggle]'));
    toggles.forEach((toggle) => this.close(toggle));
  }

  private close(toggle: HTMLElement): void {
    const menu = this.getMenu(toggle);
    if (!menu) return;

    toggle.setAttribute('aria-expanded', 'false');
    menu.setAttribute('hidden', '');
    menu.classList.remove('is-open');
  }

  private getMenu(toggle: HTMLElement): HTMLElement | null {
    const selector = toggle.dataset.hwDropdownMenu;

    if (selector) {
      return document.querySelector<HTMLElement>(selector);
    }

    const sibling = toggle.nextElementSibling;
    return sibling instanceof HTMLElement ? sibling : null;
  }
}
