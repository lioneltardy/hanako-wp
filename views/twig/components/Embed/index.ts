import { $ } from 'hanako-ts/dist/Framework';
import { Component } from 'hanako-ts/dist/Component';
import { Collection } from 'hanako-ts/dist/Collection';

export class Embed extends Component {
  constructor() {
    super('Embed', false);
  }

  public async init(): Promise<void> {
    await super.init();

    $('body').on('hw-consent-setting-external-changed', (event: any) => {
      $('iframe[data-src]').each((iframe: Collection) => {
        if (event.detail.isEnabled) {
          iframe.attr('src', iframe.attr('data-src'));
          iframe.next().removeClass('flex').addClass('hidden');
        } else {
          iframe.removeAttr('src');
          iframe.next().removeClass('hidden').addClass('flex');
        }
      });
    });

    this.success();
  }
}
