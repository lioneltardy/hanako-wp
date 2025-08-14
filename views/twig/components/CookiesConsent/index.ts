import { $ } from 'hanako-ts/dist-legacy/Framework';
import { Component } from 'hanako-ts/dist-legacy/Component';
import { Collection } from 'hanako-ts/dist-legacy/Collection';
import BS_Modal from 'bootstrap/js/dist/modal';

export class CookiesConsent extends Component {
  private settings: { [key: string]: boolean } = {};
  private mode: string;
  private modal: BS_Modal;

  constructor() {
    super('RGPDConsent', false);
  }

  public async init(): Promise<void> {
    await super.init();

    this.mode = $('#hw-cookies-consent').data('mode');

    this.modal = new BS_Modal($('#hw-cookies-consent-modal').get(0), { backdrop: true, keyboard: false });

    const cookiesClosed = localStorage.getItem('hw-cookies-defined');

    if (cookiesClosed != 'true') {
      $('#hw-cookies-consent').removeClass('d-none');
      this.initSettings();
    } else {
      this.restoreSettings();
    }

    // Show cookies modal
    $('.hw-cookies-btn-configure').on('click', (event: MouseEvent) => {
      event.preventDefault();

      this.modal.show();
    });

    // Accept all
    $('.hw-cookies-btn-agree').on('click', (event: MouseEvent) => {
      event.preventDefault();

      $('.hw-cookies-setting-switch').each((setting: Collection) => {
        console.log(setting.data('mandatory'));
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

    // Close cookies modal
    $('.hw-cookies-btn-agree, .hw-cookies-btn-decline, .hw-cookies-btn-close-modal').on('click', (event: MouseEvent) => {
      event.preventDefault();

      $('#hw-cookies-consent').addClass('d-none');

      localStorage.setItem('hw-cookies-defined', 'true');
    });

    // Switch update
    $('.hw-cookies-setting-switch').on('change', (event: Event, input: Collection) => {
      this.updateSetting(input.data('key'), input.get(0).checked);
    });

    // Do not track
    if (navigator.doNotTrack) {
      $('.hw-cookies-setting-switch[data-key="traffic"]').attr('disabled', true);
      $('.hw-do-not-track-message').removeClass('d-none');
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
    this.settings = JSON.parse(localStorage.getItem('hw-cookies-settings'));

    $('.hw-cookies-setting-switch').each((setting: Collection) => {
      setting.get(0).checked = this.settings[setting.data('key')];

      this.triggerSettingChanged(setting.data('key'), this.settings[setting.data('key')]);
    });
  }

  private triggerSettingChanged(key: string, value: boolean) {
    $('body').get(0).dispatchEvent(new CustomEvent('hw-cookies-setting-' + key + '-changed', { detail: { isEnabled: value } }));
  }
}
