

/**
  Requirements:
  
  1.)
  DataTable initialization with: DataTable({
    ...    
    scroller: true,
    scrollY: '100%',
    paging: true,
    ...
  });

  2.)
  The .dataTable_scrollBody must be positioned absolute
  within it's parent (.dataTable_scroll) with maximal
  expansion.

  Expands the scrollable table area as far as possible
  by recalculating the max-height to a concrete pixel value
  which is required by the scroller plugin to work as intended.
*/
export default class DataTableHeightFix {

  static mount($dataTable) {
    $dataTable.on('init.dt', function () {
      DataTableHeightFix.correct($dataTable);
    });
    $(window).on('resize', function() {
      DataTableHeightFix.correct($dataTable);
    });
  }

  static correct($dataTable) {
    let $scroll = $dataTable.closest('.dataTables_scroll');
    let $scrollBody = $scroll.find('.dataTables_scrollBody');
    let $scrollHead = $scroll.find('.dataTables_scrollHead');
    let tableHead_height = $scrollHead.height();
    let new_max_height = ($scroll.height() - tableHead_height);
    let the_max_height = parseInt($scrollBody.css('max-height'));
    if (the_max_height !== new_max_height) {
      $scrollBody.css('width','unset');
      $scrollBody.css('height','unset');
      $scrollBody.css('max-height', new_max_height + 'px');
      $scrollBody.css('top', tableHead_height + 'px');
      let scrollerPluginInstance = $dataTable.DataTable().settings()[0].oScroller;
      scrollerPluginInstance.measure();
    }
  }
}
