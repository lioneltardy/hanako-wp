import { $ } from 'hanako-ts/dist-legacy/Framework';
import { Component } from 'hanako-ts/dist-legacy/Component';
import { Collection } from 'hanako-ts/dist-legacy/Collection';

export class Menu extends Component {
  constructor() {
    super('Menu', false);
  }

  public async init(): Promise<void> {
    await super.init();

    $('#btn-toggle-menu').on('click', (event: MouseEvent, button: Collection) => {
      event.preventDefault();

      button.toggleClass('is-active');
      $('#main-menu').toggleClass('is-opened');
      button.attr('aria-expanded', String(button.hasClass('is-active')));
    });

    this.success();
  }
}
