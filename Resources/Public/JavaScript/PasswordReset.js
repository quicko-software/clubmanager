import $ from 'jquery';
import DocumentService from "@typo3/core/document-service.js";

let PasswordReset = {};

PasswordReset.getInputField = function (theA) {
  let formEngineInputName = theA.getAttribute('data-formengine-input-name');
  let inputSelector = 'input[data-formengine-input-name="' + formEngineInputName + '"]';
  return document.querySelector(inputSelector);
};

PasswordReset.getInputHiddenField = function (theA) {
  let formEngineInputName = theA.getAttribute('data-formengine-input-name');
  let inputSelector = 'input[name="' + formEngineInputName + '"]';
  return document.querySelector(inputSelector);
};


PasswordReset.initializeEvents = function () {
  $('a.clbmgr_password-reset').on('click', function (evt) {
    PasswordReset.handleClick(evt.currentTarget);
  });
};

PasswordReset.handleClick = function (theA) {

  let resetValue = theA.getAttribute('data-reset-value');
  let $input = $(PasswordReset.getInputField(theA));
  let $inputHidden = $(PasswordReset.getInputHiddenField(theA));

  $input[0].value = resetValue;
  $input[0].dispatchEvent(new Event('change'));
  $input[0].value = $inputHidden[0].value;
  $('button[form="EditDocumentController"][name="_savedok"]').trigger('click');

};
DocumentService.ready().then(() => {
  $(PasswordReset.initializeEvents);
});
