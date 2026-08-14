import { Component } from 'hanako-ts/dist/Component';
import { Collection } from 'hanako-ts/dist/Collection';
import { $ } from 'hanako-ts/dist/Framework';

export class Collapse extends Component {
  constructor(isDebugEnabled: boolean = false) {
    super('Collapse', false);
  }

  public async init(): Promise<void> {
    await super.init();

    const triggers = $('[data-toggle="collapse"]');

    triggers.each((trigger: Collection) => {
      this.syncTriggerState(trigger);

      trigger.on('click', (event) => {
        event.preventDefault();

        const shouldOpen = trigger.attr('aria-expanded') !== 'true';
        const groupName = trigger.data('collapseGroup');

        if (shouldOpen && groupName) {
          this.closeGroup(groupName, trigger);
        }

        this.toggle(trigger, shouldOpen);
      });
    });

    this.success();
  }

  private closeGroup(groupName: string, exceptTrigger: Collection): void {
    const groupTriggers = $(`[data-collapse-group="${groupName}"]`);

    groupTriggers.each((groupTrigger: Collection) => {
      if (groupTrigger.get(0) === exceptTrigger.get(0)) return;
      this.toggle(groupTrigger, false);
    });
  }

  private syncTriggerState(trigger: Collection): void {
    const panel = this.getPanel(trigger);
    if (!panel) return;

    const isOpen = panel.hasClass('h-auto');
    trigger.attr('aria-expanded', String(isOpen));

    if (isOpen) {
      panel.css('height', 'auto');
    }
  }

  private toggle(trigger: Collection, shouldOpen: boolean): void {
    const panel = this.getPanel(trigger);
    if (!panel || panel.data('collapsing') == 'true') return;

    if (shouldOpen) {
      this.openPanel(trigger, panel);
    } else {
      this.closePanel(trigger, panel);
    }
  }

  private openPanel(trigger: Collection, panel: Collection): void {
    trigger.attr('aria-expanded', 'true');
    panel.data('collapsing', 'true');

    panel.removeClass('h-auto');
    panel.css('height', 0);

    requestAnimationFrame(() => {
      panel.css('height', `${panel.get(0).scrollHeight}px`);
    });

    panel.get(0).addEventListener(
      'transitionend',
      () => {
        panel.data('collapsing', 'false');
        panel.addClass('h-auto');
        panel.css('height', 'auto');
      },
      { once: true }
    );
  }

  private closePanel(trigger: Collection, panel: Collection): void {
    trigger.attr('aria-expanded', 'false');
    panel.data('collapsing', 'true');

    panel.css('height', `${panel.get(0).scrollHeight}px`);
    panel.data('collapsing', 'false');
    panel.removeClass('h-auto');

    // Force reflow so the browser applies the starting height before animating to 0.
    void panel.get(0).offsetHeight;

    requestAnimationFrame(() => {
      panel.css('height', 0);
    });

    panel.get(0).addEventListener(
      'transitionend',
      () => {
        panel.data('collapsing', 'false');
        panel.css('height', '');
      },
      { once: true }
    );
  }

  private getPanel(trigger: Collection): Collection | null {
    const selector = trigger.data('target');
    if (!selector) return null;

    return $(selector);
  }
}
