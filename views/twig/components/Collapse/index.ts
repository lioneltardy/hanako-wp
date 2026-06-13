import { Component } from 'hanako-ts/dist-legacy/Component';

export class Collapse extends Component {
  constructor(isDebugEnabled: boolean = false) {
    super('Collapse', false);
  }

  public async init(): Promise<void> {
    await super.init();

    const triggers = Array.from(document.querySelectorAll<HTMLElement>('[data-hw-collapse-target]'));

    triggers.forEach((trigger) => {
      this.syncTriggerState(trigger);

      trigger.addEventListener('click', (event) => {
        event.preventDefault();

        const shouldOpen = trigger.getAttribute('aria-expanded') !== 'true';
        const groupName = trigger.dataset.hwCollapseGroup;

        if (shouldOpen && groupName) {
          this.closeGroup(groupName, trigger);
        }

        this.toggle(trigger, shouldOpen);
      });
    });

    this.success();
  }

  private closeGroup(groupName: string, exceptTrigger: HTMLElement): void {
    const groupTriggers = Array.from(document.querySelectorAll<HTMLElement>(`[data-hw-collapse-group="${groupName}"]`));

    groupTriggers.forEach((groupTrigger) => {
      if (groupTrigger === exceptTrigger) return;
      this.toggle(groupTrigger, false);
    });
  }

  private syncTriggerState(trigger: HTMLElement): void {
    const panel = this.getPanel(trigger);
    if (!panel) return;

    const isOpen = !panel.hasAttribute('hidden');
    trigger.setAttribute('aria-expanded', String(isOpen));
    panel.classList.toggle('is-open', isOpen);

    if (isOpen) {
      panel.style.height = 'auto';
    }
  }

  private toggle(trigger: HTMLElement, shouldOpen: boolean): void {
    const panel = this.getPanel(trigger);
    if (!panel || panel.dataset.collapsing === 'true') return;

    if (shouldOpen) {
      this.openPanel(trigger, panel);
    } else {
      this.closePanel(trigger, panel);
    }
  }

  private openPanel(trigger: HTMLElement, panel: HTMLElement): void {
    trigger.setAttribute('aria-expanded', 'true');
    panel.dataset.collapsing = 'true';

    panel.hidden = false;
    panel.classList.remove('is-open');
    panel.classList.add('is-collapsing');

    panel.style.height = '0px';

    requestAnimationFrame(() => {
      panel.style.height = `${panel.scrollHeight}px`;
    });

    panel.addEventListener(
      'transitionend',
      () => {
        panel.classList.remove('is-collapsing');
        panel.classList.add('is-open');
        panel.style.height = 'auto';
        delete panel.dataset.collapsing;
      },
      { once: true }
    );
  }

  private closePanel(trigger: HTMLElement, panel: HTMLElement): void {
    trigger.setAttribute('aria-expanded', 'false');
    panel.dataset.collapsing = 'true';

    panel.style.height = `${panel.scrollHeight}px`;
    panel.classList.add('is-collapsing');
    panel.classList.remove('is-open');

    // Force reflow so the browser applies the starting height before animating to 0.
    void panel.offsetHeight;

    requestAnimationFrame(() => {
      panel.style.height = '0px';
    });

    panel.addEventListener(
      'transitionend',
      () => {
        panel.classList.remove('is-collapsing');
        panel.style.height = '';
        panel.hidden = true;
        delete panel.dataset.collapsing;
      },
      { once: true }
    );
  }

  private getPanel(trigger: HTMLElement): HTMLElement | null {
    const selector = trigger.dataset.uiCollapseTarget;
    if (!selector) return null;

    return document.querySelector<HTMLElement>(selector);
  }
}
