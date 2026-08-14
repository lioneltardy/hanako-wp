import { $ } from 'hanako-ts/dist/Framework';
import { Component } from 'hanako-ts/dist/Component';
import { Collection } from 'hanako-ts/dist/Collection';

export class Menu extends Component {
  constructor() {
    super('Menu', false);
  }

  public async init(): Promise<void> {
    await super.init();

    $('#btn-toggle-menu').on('click', (event: MouseEvent, button: Collection) => {
      event.preventDefault();

      if (!button.hasAttr('data-is-active')) {
        button.attr('data-is-active', '');
        $('#main-menu').attr('data-is-open', '');
      } else {
        button.removeAttr('data-is-active');
        $('#main-menu').removeAttr('data-is-open');
      }
    });

    this.success();
  }
}
