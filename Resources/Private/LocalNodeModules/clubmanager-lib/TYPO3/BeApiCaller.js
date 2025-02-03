
import Message2Notification from "./Message2Notification";
import Notification from '@typo3/backend/notification.js';
export default class BeApiCaller {

  constructor(dataTableAjax) {
    this.dataTableAjax = dataTableAjax;
  }

  #makeFormData(postParams) {
    const formData = new FormData();
    for (var key in postParams) {
      formData.append(key, postParams[key]);
    }
    return formData;
  }

  apiCall(url, postParams, reloadTable = true, successFunction = null, okFlash = true) {
    fetch(url, {
      method: 'POST',
      body: this.#makeFormData(postParams),
    }).then((response) => {
      if (response.status === 200) {
        if (okFlash) {
          Notification.success(response.statusText, null, 5);
        }
        if (reloadTable) {
          this.dataTableAjax.reload();
        }
        if (successFunction) {
          successFunction(response);
        }
      } else {
        response.json().then((data) => {
          Message2Notification.display(data);
        });
      }
    })
    .catch((error) => Message2Notification.display({
      messageTitle: 'Internal Error',
      messageText: error
    }));
  }


  postToFlash(url, postParams, reloadTable = true, successFunction = null) {
    fetch(url, {
      method: 'POST',
      body: this.#makeFormData(postParams),
    }).then((response) => {
      response.json().then((jsonData) => {
        if (response.status === 200)  {
          if (reloadTable) {
            this.dataTableAjax.reload();
          }
          if (successFunction) {
            successFunction(response);
          }
        }
        Message2Notification.display(jsonData.flash);
      });
    })
    .catch((error) => Message2Notification.display({
      messageTitle: 'Internal Error',
      messageText: error
    }));
  }
}
