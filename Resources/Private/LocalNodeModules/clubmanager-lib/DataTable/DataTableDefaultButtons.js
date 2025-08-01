import $ from 'jquery';
export default class DataTableDefaultButtons {

  static mount($tableNode) {
    let $searchControl = $("[type=search][aria-controls=" + $tableNode[0].id + "]");
    $searchControl.on("keyup", function () {
      $tableNode.DataTable().search($(this).val()).draw();
    })
  }

  static generate(moreButtons = [],defaultSort = [0, 'asc']) {
    return [
      {
        text: '<i class="bi bi-house-x"></i>',
        attr: {
          id: "reset",
          class: "dt-button btn-right",
          title: TYPO3.lang["datatable.resettable"]
        },
        action: function (e, dt, node, config) {
          dt.search("").order(defaultSort).draw();
          let $searchControl = $("[type=search][aria-controls=" + dt.table().node().id + "]");
          $searchControl.val("");
          dt.state.clear();
          dt.colReorder.reset();
          dt.destroy();
          location.reload(); 
          
        }
      },
      {
        text: '<i class="bi bi-stars"></i>',
        attr: {
          id: "reset",
          class: "dt-button btn-right",
          title: TYPO3.lang["datatable.resetfilters"]
        },
        action: function (e, dt, node, config) {
          dt.searchPanes.clearSelections();
          dt.search("").draw();
          let $searchControl = $("[type=search][aria-controls=" + dt.table().node().id + "]");
          $searchControl.val("");
        }
      },
      {
        extend: 'colvis',
        text: '<i class="bi bi-table"></i>',
        className: 'btn-right',
      },
      {
        extend: 'csv',
        text: '<i class="bi bi-filetype-csv"></i>',
        className: 'btn-right',
        fieldSeparator: ';',
        charset: 'UTF-8',
        bom: true,   
        exportOptions: {
          columns: ':not(.notexport)'
        }           
      },
      {
        extend: 'searchPanes',
        text: '<i class="bi bi-search"></i>',
        className: 'btn-right',
      }
    ].concat(moreButtons);
  }
}
