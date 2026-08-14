import { $ } from 'hanako-ts/dist/Framework';
import { Component } from 'hanako-ts/dist/Component';
import { Collection } from 'hanako-ts/dist/Collection';

export class LazyLoader extends Component {
  private static tolerence = 100;
  private static images: Collection;
  private static backgroundImages: Collection;

  constructor() {
    super('LazyLoader', false);
  }

  public async init(): Promise<void> {
    await super.init();

    LazyLoader.refreshImageList();

    $(window).on('scroll', () => {
      LazyLoader.checkImageVisibility();
    });

    this.success();
  }

  public static checkImageVisibility() {
    LazyLoader.images.each(async (image: Collection) => {
      if (image.viewportPosition().y < $(window).height() + LazyLoader.tolerence && image.data('backgroundLoaded') != 'true') {
        const imageURL = this.get_src(image.data('hwSrc'));

        await this.preloadImage(imageURL);

        image.attr('src', imageURL);
        image.data('backgroundLoaded', 'true');
      }
    });

    LazyLoader.backgroundImages.each(async (image: Collection) => {
      if (image.viewportPosition().y < $(window).height() + LazyLoader.tolerence && image.data('backgroundLoaded') != 'true') {
        const imageURL = this.get_src(image.data('hwBackgroundImage'));

        await this.preloadImage(imageURL);

        image.css('background-image', `url('${imageURL}')`);
        image.data('backgroundLoaded', 'true');
      }
    });
  }

  public static refreshImageList() {
    LazyLoader.images = $('*[data-hw-src]');
    LazyLoader.backgroundImages = $('*[data-hw-background-image]');

    LazyLoader.checkImageVisibility();
  }

  private static get_src(image_data: string): string {
    const srcs = image_data.split(';');

    return devicePixelRatio > 1 && $(window).width() >= 768 && srcs[1] ? srcs[1] : srcs[0];
  }

  private static async preloadImage(image_data: string) {
    const img = new Image();
    img.src = this.get_src(image_data);

    return new Promise<void>((resolve) => {
      img.onload = () => {
        resolve();
      };
      img.onerror = () => {
        resolve();
      };
    });
  }
}
