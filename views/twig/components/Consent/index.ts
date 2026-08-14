import { $ } from 'hanako-ts/dist/Framework';
import { Component } from 'hanako-ts/dist/Component';
import { Collection } from 'hanako-ts/dist/Collection';

export class Consent extends Component {
  private settings: { [key: string]: boolean } = {};
  private mode: string = 'opt-in';

  constructor() {
    super('Consent', false);
  }

  public async init(): Promise<void> {
    await super.init();

    this.mode = $('#component-consent').data('mode') || 'opt-in';

    const consentClosed = localStorage.getItem('hw-consent-defined');

    if (consentClosed != 'true') {
      $('#component-consent').removeClass('hidden');
      this.initSettings();
    } else {
      this.restoreSettings();
    }

    this.initDialogCommands();

    // Accept/Decline all
    $('#btn-consent-agree, #btn-consent-decline').on('click', (event: MouseEvent, link: Collection) => {
      event.preventDefault();

      this.forceSettings(link.attr('id') === 'btn-consent-agree');

      this.saveSettings();
      this.restoreSettings();
      this.closeConsent();
    });

    $('#btn-consent-dialog').on('close', () => {
      this.closeConsent();
    });

    // Switch update
    $('.component-consent-setting-switch').on('change', (event: Event, input: Collection) => {
      this.updateSetting(input.data('key'), input.get(0).checked);
    });

    // Do not track
    if (navigator.doNotTrack) {
      $('.component-consent-setting-switch[data-key="traffic"]').attr('disabled', true);
      $('.component-do-not-track-message').removeClass('hidden');
    }

    this.success();
  }

  private initDialogCommands(): void {
    const dialog = document.getElementById('component-consent-dialog') as HTMLDialogElement | null;
    if (!dialog) return;

    dialog.addEventListener('close', () => this.closeConsent());

    document.querySelectorAll<HTMLElement>('[commandfor="component-consent-dialog"]').forEach((trigger) => {
      trigger.addEventListener('click', (event) => {
        event.preventDefault();

        if (trigger.getAttribute('command') === 'show-modal') {
          if (typeof dialog.showModal === 'function') {
            if (!dialog.open) dialog.showModal();
            return;
          }

          dialog.setAttribute('open', '');
          return;
        }

        if (trigger.getAttribute('command') !== 'close') return;

        if (typeof dialog.close === 'function') {
          if (dialog.open) dialog.close();
          return;
        }

        if (!dialog.hasAttribute('open')) return;

        dialog.removeAttribute('open');
        dialog.dispatchEvent(new Event('close'));
      });
    });
  }

  private initSettings() {
    $('.component-consent-setting-switch').each((setting: Collection) => {
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
    localStorage.setItem('hw-consent-settings', JSON.stringify(this.settings));
  }

  private restoreSettings() {
    const storedSettings = localStorage.getItem('hw-consent-settings');
    this.settings = storedSettings ? JSON.parse(storedSettings) : {};

    $('.component-consent-setting-switch').each((setting: Collection) => {
      if (typeof this.settings[setting.data('key')] !== 'boolean') {
        this.settings[setting.data('key')] = setting.data('mandatory') !== undefined;
      }

      setting.get(0).checked = this.settings[setting.data('key')];

      this.triggerSettingChanged(setting.data('key'), this.settings[setting.data('key')]);
    });
  }

  private forceSettings(value: boolean) {
    $('.component-consent-setting-switch').each((setting: Collection) => {
      if (setting.data('mandatory') == 'true') return;
      if (setting.data('key') === 'traffic' && navigator.doNotTrack) value = false;
      this.settings[setting.data('key')] = setting.get(0).checked = value;
    });
  }

  private triggerSettingChanged(key: string, value: boolean) {
    $('body').get(0).dispatchEvent(new CustomEvent('hw-consent-setting-' + key + '-changed', { detail: { isEnabled: value } }));
  }

  private closeConsent() {
    $('#component-consent').addClass('hidden');

    localStorage.setItem('hw-consent-defined', 'true');
  }
}
