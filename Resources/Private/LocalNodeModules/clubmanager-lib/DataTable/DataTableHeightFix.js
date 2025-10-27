

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
  The .dt-scroll-body must be positioned absolute
  within it's parent (.dt-scroll) with maximal
  expansion.

  Expands the scrollable table area as far as possible
  by recalculating the max-height to a concrete pixel value
  which is required by the scroller plugin to work as intended.
*/
import $ from 'jquery';

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
    let $scroll = $dataTable.closest('.dt-scroll');
    let $scrollBody = $scroll.find('.dt-scroll-body');
    let $scrollHead = $scroll.find('.dt-scroll-head');
    let tableHead_height = $scrollHead.height();
    let new_max_height = ($scroll.height() - tableHead_height);
    let the_max_height = parseInt($scrollBody.css('max-height'));
    if (the_max_height !== new_max_height) {
      $scrollBody.css('width','unset');
      $scrollBody.css('height','unset');
      $scrollBody.css('max-height', new_max_height + 'px');
      let scrollerPluginInstance = $dataTable.DataTable().settings()[0].oScroller;
      scrollerPluginInstance.measure();
    }
  }
}
