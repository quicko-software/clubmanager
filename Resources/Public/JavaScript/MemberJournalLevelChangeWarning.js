import $ from 'jquery';

/**
 * Validates level_change journal entries:
 * 1. Bug 7: Blocks saving if old_level == new_level (hard error)
 * 2. CR5: Shows warning if effective_date < today for status/level changes
 * 3. CR6: Warns on activation without email address
 *
 * Uses the same pattern as EmailVerificationTokenReset - intercepts save button click.
 */
const MemberJournalLevelChangeWarning = {
  formSelector: 'form[name="editform"]',
  configSelector: '.clbmgr_member-journal-level-warning',
  saveButtonSelector: 'button[name="_savedok"]',
  entryTypeSelector: 'select[name^="data[tx_clubmanager_domain_model_memberjournalentry]["][name$="[entry_type]"]',

  isPastDateConfirmed: false,
  isNoEmailConfirmed: false,

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
      // Bug 7: Prüfe auf gleiche Level (harte Blockierung, kein Weiter möglich)
      const sameLevelEntry = self.findSameLevelEntry(form);
      if (sameLevelEntry) {
        event.preventDefault();
        event.stopImmediatePropagation();

        top.TYPO3.Modal.confirm(
          config.sameLevelTitle || 'Validation Error',
          config.sameLevelText || 'Level change not possible: New level is the same as the current level.',
          top.TYPO3.Severity.error,
          [
            {
              text: config.okLabel || 'OK',
              btnClass: 'btn-default',
              active: true,
              trigger: function() {
                top.TYPO3.Modal.dismiss();
              }
            }
          ]
        );

        return false;
      }

      // CR3: Prüfe auf gleichen Status (harte Blockierung, kein Weiter möglich)
      const sameStatusEntry = self.findSameStatusEntry(form, config.memberState);
      if (sameStatusEntry) {
        event.preventDefault();
        event.stopImmediatePropagation();

        top.TYPO3.Modal.confirm(
          config.sameStatusTitle || 'Validation Error',
          config.sameStatusText || 'Status change not possible: Target status is the same as the current status.',
          top.TYPO3.Severity.error,
          [
            {
              text: config.okLabel || 'OK',
              btnClass: 'btn-default',
              active: true,
              trigger: function() {
                top.TYPO3.Modal.dismiss();
              }
            }
          ]
        );

        return false;
      }

      // CR5: Warnung bei Vergangenheitsdatum (weiche Warnung, kann fortfahren)
      if (!self.isPastDateConfirmed && self.hasUnprocessedPastEntry(form)) {
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
                self.isPastDateConfirmed = true;
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
      }

      // CR6: Warnung bei Aktivierung ohne E-Mail (weiche Warnung, kann fortfahren)
      if (!self.isNoEmailConfirmed && self.hasActivationWithoutEmail(form, config.activeState)) {
        event.preventDefault();
        event.stopImmediatePropagation();

        top.TYPO3.Modal.confirm(
          config.noEmailTitle || 'Missing email address',
          config.noEmailText || 'No email address is set. Activation can continue, but automatic login communication is not possible.',
          top.TYPO3.Severity.warning,
          [
            {
              text: config.okLabel || 'Continue',
              btnClass: 'btn-warning',
              trigger: function() {
                top.TYPO3.Modal.dismiss();
                self.isNoEmailConfirmed = true;
                $(self.saveButtonSelector).first().trigger('click');
              }
            },
            {
              text: config.cancelLabel || 'Cancel',
              btnClass: 'btn-default',
              active: true,
              trigger: function() {
                top.TYPO3.Modal.dismiss();
              }
            }
          ]
        );

        return false;
      }

      self.isPastDateConfirmed = false;
      self.isNoEmailConfirmed = false;
      return true;
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
      cancelLabel: element.getAttribute('data-dialog-cancel-button-label') || '',
      sameLevelTitle: element.getAttribute('data-same-level-title') || '',
      sameLevelText: element.getAttribute('data-same-level-text') || '',
      sameStatusTitle: element.getAttribute('data-same-status-title') || '',
      sameStatusText: element.getAttribute('data-same-status-text') || '',
      noEmailTitle: element.getAttribute('data-no-email-title') || '',
      noEmailText: element.getAttribute('data-no-email-text') || '',
      memberState: parseInt(element.getAttribute('data-member-state') || '0', 10),
      activeState: parseInt(element.getAttribute('data-active-state') || '2', 10)
    };
  },

  /**
   * Bug 7: Findet Level-Change-Einträge wo old_level == new_level
   * Gibt den recordId des ersten gefundenen Eintrags zurück, oder null.
   */
  findSameLevelEntry(form) {
    const entryTypeFields = form.querySelectorAll(this.entryTypeSelector);
    if (!entryTypeFields.length) {
      return null;
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

      // Bereits verarbeitete Einträge überspringen
      if (this.isProcessed(form, recordId)) {
        continue;
      }

      const oldLevel = this.getFieldValue(form, recordId, 'old_level');
      const newLevel = this.getFieldValue(form, recordId, 'new_level');

      // Nur prüfen wenn beide Werte vorhanden sind
      if (oldLevel === null || newLevel === null) {
        continue;
      }

      if (oldLevel === newLevel) {
        return recordId;
      }
    }

    return null;
  },

  /**
   * CR3: Findet Status-Change-Einträge wo target_state == memberState
   * Gibt den recordId des ersten gefundenen Eintrags zurück, oder null.
   */
  findSameStatusEntry(form, memberState) {
    const entryTypeFields = form.querySelectorAll(this.entryTypeSelector);
    if (!entryTypeFields.length) {
      return null;
    }

    for (const entryTypeField of entryTypeFields) {
      const entryType = entryTypeField.value || '';
      if (entryType !== 'status_change') {
        continue;
      }

      const recordId = this.getInlineRecordId(entryTypeField.getAttribute('name') || '');
      if (!recordId) {
        continue;
      }

      // Bereits verarbeitete Einträge überspringen
      if (this.isProcessed(form, recordId)) {
        continue;
      }

      const targetState = this.getFieldValue(form, recordId, 'target_state');

      // Nur prüfen wenn target_state vorhanden ist
      if (targetState === null) {
        continue;
      }

      if (targetState === memberState) {
        return recordId;
      }
    }

    return null;
  },

  /**
   * Holt den Wert eines Feldes für einen Journal-Eintrag
   */
  getFieldValue(form, recordId, fieldName) {
    const fullFieldName = `data[tx_clubmanager_domain_model_memberjournalentry][${recordId}][${fieldName}]`;

    // Versuche zuerst das hidden field (enthält den tatsächlichen Wert)
    let input = form.querySelector(`input[name="${fullFieldName}"]`);
    if (input) {
      const value = (input.value || '').trim();
      if (value !== '') {
        return parseInt(value, 10);
      }
    }

    // Fallback: Select-Element
    const select = form.querySelector(`select[name="${fullFieldName}"]`);
    if (select) {
      const value = (select.value || '').trim();
      if (value !== '') {
        return parseInt(value, 10);
      }
    }

    // Fallback: data-formengine-input-name
    input = form.querySelector(`input[data-formengine-input-name="${fullFieldName}"]`);
    if (input) {
      const value = (input.value || '').trim();
      if (value !== '') {
        return parseInt(value, 10);
      }
    }

    return null;
  },

  /**
   * Check if any status/level entry has:
   * - entry_type = 'level_change' or 'status_change'
   * - effective_date < today
   * - processed is empty (not yet processed)
   */
  hasUnprocessedPastEntry(form) {
    const entryTypeFields = form.querySelectorAll(this.entryTypeSelector);
    if (!entryTypeFields.length) {
      return false;
    }

    for (const entryTypeField of entryTypeFields) {
      const entryType = entryTypeField.value || '';
      if (entryType !== 'level_change' && entryType !== 'status_change') {
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

  hasActivationWithoutEmail(form, activeState) {
    if (this.getMemberEmail(form) !== '') {
      return false;
    }

    const entryTypeFields = form.querySelectorAll(this.entryTypeSelector);
    if (!entryTypeFields.length) {
      return false;
    }

    for (const entryTypeField of entryTypeFields) {
      const entryType = entryTypeField.value || '';
      if (entryType !== 'status_change') {
        continue;
      }

      const recordId = this.getInlineRecordId(entryTypeField.getAttribute('name') || '');
      if (!recordId) {
        continue;
      }

      if (this.isProcessed(form, recordId)) {
        continue;
      }

      const targetState = this.getFieldValue(form, recordId, 'target_state');
      if (targetState === activeState) {
        return true;
      }
    }

    return false;
  },

  getMemberEmail(form) {
    let input = form.querySelector('input[name^="data[tx_clubmanager_domain_model_member]["][name$="[email]"]');
    if (!input) {
      input = form.querySelector('input[data-formengine-input-name^="data[tx_clubmanager_domain_model_member]["][data-formengine-input-name$="[email]"]');
    }
    return input ? (input.value || '').trim() : '';
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
