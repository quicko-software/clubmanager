
import RecordDeletionPostBuilder from './RecordDeletionPostBuilder';

var sprintf = require('sprintf-js').sprintf;

export default class BackendInteraction {

  static deleteRecords(Modal, Severity, dataTable, rows, tableName, actionUrl) {
    var isMultiRows = rows.length > 1;
   
    var modalTitle = TYPO3.lang[isMultiRows ? 'clip_deleteMarked' : 'label.confirm.delete_record.title'];
    var modalContent = TYPO3.lang[isMultiRows ? 'clip_deleteMarkedWarning' : 'label.confirm.delete_record.content'];
    modalContent = sprintf(modalContent, TYPO3.lang[tableName]);
    var noButtonText = TYPO3.lang[isMultiRows ? 'button.close' : 'buttons.confirm.delete_record.no'];
    var yesButtonText = TYPO3.lang[isMultiRows ? 'button.delete' : 'buttons.confirm.delete_record.yes'];

    Modal.confirm(modalTitle, modalContent, Severity.warning, [
      { text: noButtonText, btnClass: 'btn-default', name: 'no'},
      { text: yesButtonText, btnClass: 'btn-warning', name: 'yes', active: true}
    ]
    ).on('button.clicked', function (event) {
      Modal.dismiss();
      if (event.target.name !== 'yes') {
        return;
      }

      let selectedUids = [];
      for (let i = 0; i < rows.length; i++) {
        let uid = rows[i]["uid"];
        selectedUids.push(uid);
      }
      let deletePostData = RecordDeletionPostBuilder.build(tableName, selectedUids);

      $.ajax({
        url: actionUrl,
        data: deletePostData,
        method: 'POST'
      }).done(function () {
        dataTable.rows(function (idx, data, node) {
          return selectedUids.includes(data["uid"]);
        })
        .remove()
        .draw();
        dataTable.draw();
      }).fail(function () {
        alert("error");
      })
    });
  }
}
