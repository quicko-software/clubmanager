export default class ContentBlocker {

  static mount() {
    let $blockContentElements = $('.block-content');
    $blockContentElements.each(function (index) {
      return new ContentBlocker($($blockContentElements[index]));
    });
  }

  allow() {
    var $alwaysCheckbox = $('.allow-always', this.$element);
    if (this.getMode() == "cookieman" && window.cookieman) {
      if ($alwaysCheckbox.is(':checked')) {
        cookieman.consent(this.getConsentGroupId());
      }
    } else {
      this.setCookie(this.getCookieName(),true,30);
    }
    ContentBlocker.loadContent(this.$element);
  }

  constructor($element) {
    this.$element = $element;
    let $allowButton = $(".contentAllowButton", this.$element);
    if (this.getMode() == "cookieman" && window.cookieman) { 
      if(cookieman.hasConsented(this.getCookieName())) {
        ContentBlocker.loadContent(this.$element);
      }
    } else {
      if(this.getCookie(this.getCookieName())) {
        ContentBlocker.loadContent(this.$element);
      }
    }
    $allowButton.on("click", (e) => {
      e.preventDefault();
      this.allow()
    });
    
  }

  static load(groupdId) {
    let $blockContentElements = $('[data-consent-groupid="'+ groupdId + '"].block-content');
    $blockContentElements.each(function (index) {
      ContentBlocker.loadContent($($blockContentElements[index]));
    });
  }

  static loadContent($element) {
    let type = $element.data("type");
    switch (type) {
      case "iframe":
        ContentBlocker.loadContentIFrame($element);
        break;
      case "userEvent":
        ContentBlocker.fireUserEvent($element);
        break;
    }
  }

  static fireUserEvent($element) {
    const event = new Event($element.data("event-name"));
    $element.html("");
    document.dispatchEvent(event);
  }

  static loadContentIFrame($element) {
    $element.html('<iframe width="100%" height="100%" src="' + $element.data("src") + '"></iframe>')
  }

  getCookieName() {
    return this.getConsentGroupId() + "-allowed";    
  }

  getConsentGroupId() {
    return this.$element.data("consent-group-id")
  }

  getMode() {
    return this.$element.data("content-blocker-mode")
  }

  setCookie(name, value, days) {
    var expires = "";
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
  }

  getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  eraseCookie(name) {
    document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
  }
}




