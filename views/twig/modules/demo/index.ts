import { $ } from 'hanako-ts/dist-legacy/Framework';
import { Component } from 'hanako-ts/dist-legacy/Component';
import { Collection } from 'hanako-ts/dist-legacy/Collection';

export class Demo extends Component {
  constructor() {
    super('Demo', false);
  }

  public async init(): Promise<void> {
    await super.init();

    $('body').on('hw-cookies-setting-external-changed', (event: any) => {

      $('.video').each((video: Collection) => {
        const iframe = video.find('iframe');

        if (event.detail.isEnabled) {
          iframe.attr('src', iframe.attr('data-src'));
          video.find('.video-disabled-mask').addClass('hidden');
        } else {
          iframe.removeAttr('src');
          video.find('.video-disabled-mask').removeClass('hidden');
        }
      });
    });

    this.success();
  }
}
