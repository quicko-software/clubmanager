/**
 * Module: TYPO3/CMS/Clubmanager/SearchLocation
 *
 * JavaScript to handle search requests
 * @exports TYPO3/CMS/GsaTemplate/CmSearchLocation
 */
define(['jquery', 'TYPO3/CMS/Core/Ajax/AjaxRequest'], function ($, AjaxRequest) {
  'use strict';
  /**
   * @exports TYPO3/CMS/Clubmanager/SearchLocation
   */
  var SearchLocation = {};

  /**
   * @param {int} id
   */
  SearchLocation.run = function (uid, name, tableName, fieldName, mapping, target) {

    var searchString = "";
    for (var i = 0; i < mapping.length; i++) {
      var dataId = "data[" + tableName + "][" + uid + "][" + mapping[i] + "]";
      var input = $("[data-formengine-input-name='" + dataId + "']");
      searchString += input.val() + " ";
    }
    searchString = searchString.trim();
    if (!searchString) {
      return;
    }
    new AjaxRequest('https://nominatim.openstreetmap.org/search')
      .withQueryArguments({
        format: "json",
        limit: 1,
        q: searchString
      }
      )
      .get()
      .then(async function (response) {
        const result = await response.resolve();
        if (result && result.length == 1) {
          var data = result[0];
          for (const [key, value] of Object.entries(target)) {
            var dataId = "data[" + tableName + "][" + uid + "][" + key + "]";
            var input = $("[data-formengine-input-name='" + dataId + "']");
            input.val(data[value]);
            TBE_EDITOR.fieldChanged(tableName, uid, key, dataId);
          }
          top.TYPO3.Notification.success('Fertig.');
        } else {
          top.TYPO3.Notification.warning('Keine Daten gefunden.');
        }
      });

  };

  /**
   * initializes events using deferred bound to document
   * so AJAX reloads are no problem
   */
  SearchLocation.initializeEvents = function () {

    $('.clbmgr_search_location_button').on('click', function (evt) {
      evt.preventDefault();

      SearchLocation.run($(this).data("uid"), $(this).data("name"), $(this).data("tablename"), $(this).data("fieldname"), $(this).data("mapping"), $(this).data("target"));
    });
  };

  $(SearchLocation.initializeEvents);

  return SearchLocation;
});

