export default class DataTablesUtil {

  // Find the index of the column that is configured to show the property dataRef.
  // E.g. DataTablesUtil.findIndexByDataRef([{data:'uid'},{data:'peng'}], 'peng') => 1
  static findIndexByDataRef(columns, dataRef) {
    const idx = columns.findIndex(function (columnDef) {
      return columnDef.data === dataRef;
    });
    if (idx === -1) {
      throw "logic error - no such column name '" + dataRef + "'";
    }
    return idx;
  }

  static insertColumnAfter(columns, newColumnDef, dataName) {
    const idx = DataTablesUtil.findIndexByDataRef(columns, dataName);
    columns.splice(idx + 1, 0, newColumnDef);
  }

  // Get an array of the selected row's data, e.g. all uids:
  // DataTablesUtil.getFromSelectedRows(dataTable, 'uid'); -> [13,5774,47] or []
  static getFromSelectedRows(dataTable, colDataName) {
    let selectedRows = dataTable.rows({ selected: true }).data();
    let result = [];
    for (let i = 0; i < selectedRows.length; i++) {
      result.push(selectedRows[i][colDataName]);
    }
    return result;
  }

  static insertIntoButtonCollection(buttons, buttonCollectionId, newButtonDef) {
    let idx = this.#findButtonPos(buttons, buttonCollectionId);
    if (idx === -1) {
      throw 'unable to find button collection with id: '.buttonCollectionId;
    }
    buttons[idx].buttons.push(newButtonDef);
  }

  static #findButtonPos(buttons, buttonId) {
    for (let i=0; i<buttons.length; ++i) {
      let button = buttons[i];
      let isCollection = (button.extend === 'collection');
      let hasThatId = (button.attr && button.attr.id === buttonId);
      let hasChildButtons = button.buttons instanceof Array;
      if (isCollection && hasThatId && hasChildButtons) {
        return i;
      }
    }
    return -1;
  }
}
