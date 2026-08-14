import { $ } from 'hanako-ts/dist/Framework';
import { Component } from 'hanako-ts/dist/Component';
import { Collection } from 'hanako-ts/dist/Collection';

export class ScrollSpy extends Component {
  private links: Collection;
  private sections: Collection;
  private headerHeight: number;

  constructor() {
    super('ScrollSpy', false);

    this.headerHeight = 0;
  }

  public async init() {
    await super.init();

    this.sections = $('[data-scrollspy-section]');
    this.links = $('[data-scrollspy-menu] a');

    this.links.on('click', (event: Event, link: Collection) => {
      event.preventDefault();

      const targetHref = link.attr('href');
      const targetId = targetHref?.startsWith('#') ? targetHref.slice(1) : null;
      const target = targetId ? document.getElementById(targetId) : null;
      const targetPosition = target ? $(target).position() : null;

      if (targetPosition && targetPosition.y !== undefined) $.scrollTo(targetPosition.y - this.headerHeight, 500);
    });

    $(window).on('scroll', () => {
      this.spy();
    });

    $(window).on('resize', () => {
      this.updateHeaderHeight();
    });

    this.updateHeaderHeight();
    this.spy();

    this.success();
  }

  public spy(): void {
    let currentID = this.sections.eq(0).attr('id');

    this.sections.each((section: Collection) => {
      const targetPosition = section.viewportPosition();

      if (targetPosition && targetPosition.y !== undefined && Math.floor(targetPosition.y) <= this.headerHeight) currentID = section.attr('id');
    });

    if (currentID) {
      const activeLinks = this.links.search('[href="#' + CSS.escape(currentID) + '"]');

      if (activeLinks.length > 0) {
        this.links.removeAttr('data-is-active');
        activeLinks.attr('data-is-active', '');
      }
    }
  }

  private updateHeaderHeight() {
    this.headerHeight = parseFloat($('body').css('padding-top')) || 0;
  }
}
