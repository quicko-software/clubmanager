import AjaxRequest from "@typo3/core/ajax/ajax-request.js";
import $ from 'jquery';


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
          input.trigger('change');
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


