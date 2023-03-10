export default class Message2Notification {
  static display(Notification, data) {
    if (!data) {
      return;
    }
    switch (data.messageType) {
      case 'success':
        Notification.success(data.messageTitle, data.messageText, 10, []);
        break;
      case 'info':
        Notification.info(data.messageTitle, data.messageText, 10, []);
        break;
      case 'warning':
        Notification.warning(data.messageTitle, data.messageText, 10, []);
        break;
      default:
        Notification.error(data.messageTitle, data.messageText, 0, []);
    }    
  }
}