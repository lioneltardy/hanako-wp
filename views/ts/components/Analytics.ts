import { $ } from 'hanako-ts/dist/Framework';
import { Component } from 'hanako-ts/dist/Component';

export class Analytics extends Component {
  private GA_ID: string = '';
  constructor() {
    super('Analytics', false);
  }

  public async init(): Promise<void> {
    await super.init();

    $('body').on('hw-consent-setting-traffic-changed', (event: any) => {
      if (event.detail.isEnabled) {
        //this.enableGA();
        this.enableMatomo();
      }
    });

    this.success();
  }

  private enableMatomo() {
    let _paq = ((<any>window)._paq = (<any>window)._paq || []);

    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    _paq.push(['alwaysUseSendBeacon']);
    _paq.push(['setTrackerUrl', '//dev-local.local/wp-content/plugins/matomo/app/matomo.php']);
    _paq.push(['setSiteId', '1']);

    const scriptTag = document.createElement('script');
    scriptTag.type = 'text/javascript';
    scriptTag.async = true;
    scriptTag.src = '/wp-content/uploads/matomo/matomo.js';

    const s = document.getElementsByTagName('script')[0];
    s?.parentNode?.insertBefore(scriptTag, s);
  }

  private enableGA() {
    const scriptTag = document.createElement('script');
    scriptTag.type = 'text/javascript';
    scriptTag.async = true;
    scriptTag.src = 'https://www.googletagmanager.com/gtag/js?id=' + this.GA_ID;

    const s = document.getElementsByTagName('script')[0];
    s?.parentNode?.insertBefore(scriptTag, s);

    (<any>window).dataLayer = (<any>window).dataLayer || [];
    function gtag(name: string, data: any) {
      (<any>(<any>window).dataLayer).push(arguments);
    }
    gtag('js', new Date());
    gtag('config', this.GA_ID);
  }
}
