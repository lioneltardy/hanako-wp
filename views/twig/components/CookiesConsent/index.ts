import { $ } from 'hanako-ts/dist-legacy/Framework';
import { Component } from 'hanako-ts/dist-legacy/Component';
import { Collection } from 'hanako-ts/dist-legacy/Collection';

export class CookiesConsent extends Component {
  private settings: { [key: string]: boolean } = {};
  private mode: string = 'opt-in';

  constructor() {
    super('RGPDConsent', false);
  }

  public async init(): Promise<void> {
    await super.init();

    this.mode = $('#hw-cookies-consent').data('mode') || 'opt-in';

    const cookiesClosed = localStorage.getItem('hw-cookies-defined');

    if (cookiesClosed != 'true') {
      $('#hw-cookies-consent').removeClass('hidden');
      this.initSettings();
    } else {
      this.restoreSettings();
    }

    // Accept all
    $('.hw-cookies-btn-agree').on('click', (event: MouseEvent) => {
      event.preventDefault();

      $('.hw-cookies-setting-switch').each((setting: Collection) => {
        if (setting.data('mandatory') !== undefined) return;
        this.settings[setting.data('key')] = setting.get(0).checked = true;
      });

      this.saveSettings();
      this.restoreSettings();
    });

    // Decline all
    $('.hw-cookies-btn-decline').on('click', (event: MouseEvent) => {
      event.preventDefault();

      $('.hw-cookies-setting-switch').each((setting: Collection) => {
        if (setting.data('mandatory') !== undefined) return;
        this.settings[setting.data('key')] = setting.get(0).checked = false;
      });

      this.saveSettings();
      this.restoreSettings();
    });

    $('.hw-cookies-btn-agree, .hw-cookies-btn-decline').on('click', (event: MouseEvent) => {
      event.preventDefault();

      this.closeCookiesConsent();
    });

    $('#hw-coolies-consent-dialog').on('close', () => {
      this.closeCookiesConsent();
    });

    // Switch update
    $('.hw-cookies-setting-switch').on('change', (event: Event, input: Collection) => {
      this.updateSetting(input.data('key'), input.get(0).checked);
    });

    // Do not track
    if (navigator.doNotTrack) {
      $('.hw-cookies-setting-switch[data-key="traffic"]').attr('disabled', true);
      $('.hw-do-not-track-message').removeClass('hidden');
    }

    this.success();
  }

  private initSettings() {
    $('.hw-cookies-setting-switch').each((setting: Collection) => {
      let value = this.mode == 'opt-in' ? false : true;
      if (setting.data('mandatory') !== undefined) value = true;
      if (setting.data('key') === 'traffic' && navigator.doNotTrack) value = false;

      this.settings[setting.data('key')] = value
    });

    this.saveSettings();
    this.restoreSettings();
  }

  private updateSetting(key: string, value: boolean) {
    this.settings[key] = value;

    this.triggerSettingChanged(key, value);
    this.saveSettings();
  }

  private saveSettings() {
    localStorage.setItem('hw-cookies-settings', JSON.stringify(this.settings));
  }

  private restoreSettings() {
    const storedSettings = localStorage.getItem('hw-cookies-settings');
    this.settings = storedSettings ? JSON.parse(storedSettings) : {};

    $('.hw-cookies-setting-switch').each((setting: Collection) => {
      if (typeof this.settings[setting.data('key')] !== 'boolean') {
        this.settings[setting.data('key')] = setting.data('mandatory') !== undefined;
      }

      setting.get(0).checked = this.settings[setting.data('key')];

      this.triggerSettingChanged(setting.data('key'), this.settings[setting.data('key')]);
    });
  }

  private triggerSettingChanged(key: string, value: boolean) {
    $('body').get(0).dispatchEvent(new CustomEvent('hw-cookies-setting-' + key + '-changed', { detail: { isEnabled: value } }));
  }

  private closeCookiesConsent() {
    $('#hw-cookies-consent').addClass('hidden');

    localStorage.setItem('hw-cookies-defined', 'true');
  }
}
