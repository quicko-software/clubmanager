
import $ from 'jquery';
import ContentBlocker from "./ContentBlocker";

$(function () {
  ContentBlocker.mount();
});

window.ContentBlocker = ContentBlocker;