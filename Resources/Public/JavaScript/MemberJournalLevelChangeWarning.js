import $ from 'jquery';

/**
 * Shows a warning dialog when saving a member with level_change journal entries
 * that have an effective_date in the past or today AND are not yet processed.
 *
 * Uses the same pattern as EmailVerificationTokenReset - intercepts save button click.
 */
const MemberJournalLevelChangeWarning = {
  formSelector: 'form[name="editform"]',
  configSelector: '.clbmgr_member-journal-level-warning',
  saveButtonSelector: 'button[name="_savedok"]',
  entryTypeSelector: 'select[name^="data[tx_clubmanager_domain_model_memberjournalentry]["][name$="[entry_type]"]',

  isConfirmed: false,

  initialize() {
    const config = this.getDialogConfig();
    if (!config) {
      return;
    }

    const form = document.querySelector(this.formSelector);
    if (!form) {
      return;
    }

    const self = this;
    $(document).on('click', this.saveButtonSelector, function(event) {
      if (self.isConfirmed) {
        self.isConfirmed = false;
        return true;
      }

      if (!self.hasUnprocessedPastLevelChange(form)) {
        return true;
      }

      event.preventDefault();
      event.stopImmediatePropagation();

      top.TYPO3.Modal.confirm(
        config.title,
        config.text,
        top.TYPO3.Severity.warning,
        [
          {
            text: config.okLabel,
            btnClass: 'btn-warning',
            trigger: function() {
              top.TYPO3.Modal.dismiss();
              self.isConfirmed = true;
              $(self.saveButtonSelector).first().trigger('click');
            }
          },
          {
            text: config.cancelLabel,
            btnClass: 'btn-default',
            active: true,
            trigger: function() {
              top.TYPO3.Modal.dismiss();
            }
          }
        ]
      );

      return false;
    });
  },

  getDialogConfig() {
    const element = document.querySelector(this.configSelector);
    if (!element) {
      return null;
    }

    return {
      title: element.getAttribute('data-dialog-title') || '',
      text: element.getAttribute('data-dialog-text') || '',
      okLabel: element.getAttribute('data-dialog-ok-button-label') || '',
      cancelLabel: element.getAttribute('data-dialog-cancel-button-label') || ''
    };
  },

  /**
   * Check if any level_change entry has:
   * - entry_type = 'level_change'
   * - effective_date <= today
   * - processed is empty (not yet processed)
   */
  hasUnprocessedPastLevelChange(form) {
    const entryTypeFields = form.querySelectorAll(this.entryTypeSelector);
    if (!entryTypeFields.length) {
      return false;
    }

    for (const entryTypeField of entryTypeFields) {
      const entryType = entryTypeField.value || '';
      if (entryType !== 'level_change') {
        continue;
      }

      const recordId = this.getInlineRecordId(entryTypeField.getAttribute('name') || '');
      if (!recordId) {
        continue;
      }

      if (this.isProcessed(form, recordId)) {
        continue;
      }

      const effectiveTimestamp = this.getEffectiveDateTimestamp(form, recordId);
      if (!effectiveTimestamp) {
        continue;
      }

      if (this.isPast(effectiveTimestamp)) {
        return true;
      }
    }

    return false;
  },

  getInlineRecordId(fieldName) {
    const match = fieldName.match(
      /^data\[tx_clubmanager_domain_model_memberjournalentry]\[([^\]]+)]\[entry_type]$/
    );
    return match ? match[1] : null;
  },

  /**
   * Check if the journal entry has been processed (processed field is set)
   */
  isProcessed(form, recordId) {
    const fieldName = `data[tx_clubmanager_domain_model_memberjournalentry][${recordId}][processed]`;

    let input = form.querySelector(`input[name="${fieldName}"]`);
    if (!input) {
      input = form.querySelector(`input[data-formengine-input-name="${fieldName}"]`);
    }

    if (!input) {
      return false;
    }

    const value = (input.value || '').trim();
    return value !== '' && value !== '0';
  },

  getEffectiveDateTimestamp(form, recordId) {
    const fieldName = `data[tx_clubmanager_domain_model_memberjournalentry][${recordId}][effective_date]`;

    let input = form.querySelector(`input[name="${fieldName}"]`);
    if (input) {
      const value = this.parseTimestamp(input.value || '');
      if (value) {
        return value;
      }
    }

    input = form.querySelector(`input[data-formengine-input-name="${fieldName}"]`);
    if (input) {
      return this.parseTimestamp(input.value || '');
    }

    return null;
  },

  parseTimestamp(value) {
    const rawValue = (value || '').trim();
    if (rawValue === '') {
      return null;
    }

    if (/^\d+$/.test(rawValue)) {
      const numericValue = parseInt(rawValue, 10);
      if (numericValue === 0) {
        return null;
      }
      return numericValue > 9999999999 ? Math.floor(numericValue / 1000) : numericValue;
    }

    const germanDateMatch = rawValue.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/);
    if (germanDateMatch) {
      const date = new Date(
        Number(germanDateMatch[3]),
        Number(germanDateMatch[2]) - 1,
        Number(germanDateMatch[1])
      );
      return Math.floor(date.getTime() / 1000);
    }

    const parsedDate = Date.parse(rawValue);
    if (!Number.isNaN(parsedDate)) {
      return Math.floor(parsedDate / 1000);
    }

    return null;
  },

  /**
   * CR5: Prüft ob das Datum in der Vergangenheit liegt (NICHT heute).
   * Nur bei effective_date < heute soll die Warnung erscheinen.
   */
  isPast(timestamp) {
    const effectiveDate = new Date(timestamp * 1000);
    effectiveDate.setHours(0, 0, 0, 0);

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // CR5: Nur < (strikt kleiner), nicht <= (kleiner oder gleich)
    return effectiveDate.getTime() < today.getTime();
  }
};

$(function() {
  MemberJournalLevelChangeWarning.initialize();
});
