import { $ } from 'hanako-ts/dist/Framework';
import { Component } from 'hanako-ts/dist/Component';
import { Collection } from 'hanako-ts/dist/Collection';

export class Ui extends Component {
  constructor() {
    super('UI', false);
  }

  public async init(): Promise<void> {
    await super.init();

    this.initFileUpload();

    this.success();
  }

  private initFileUpload(): void {
    $('input[type="file"]').on('change', (event: Event, input: Collection) => {
      // Check file·s size
      const maxSize = parseInt(input.data('maxSize')) * 1000;

      if (maxSize > 0) {
        const files = (event.target as HTMLInputElement).files;
        if (files && files.length > 0) {
          const fileSize = files[0].size;
          if (fileSize > maxSize) {
            const formatFileSize = (size: number): string => {
              if (size >= 1000 * 1000) {
                return `${(size / (1000 * 1000)).toFixed(0)} MB`;
              }

              if (size >= 1000) {
                return `${(size / 1000).toFixed(0)} KB`;
              }

              return `${size} bytes`;
            };

            alert(
              `File size exceeds the maximum allowed size of ${formatFileSize(maxSize)}. Selected file size: ${formatFileSize(fileSize)}.`
            );
            input.val('');
            return;
          }
        }
      }

      // Update label text with the selected file name
      const fileName = (event.target as HTMLInputElement).files?.[0]?.name || '';
      const label = input.parent().find('span').first();
      label.text(fileName || 'Click to select a file');
    });
  }
}
