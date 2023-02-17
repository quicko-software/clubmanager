
export default class BeUrlBuilder {

  constructor(config_beUri) {
    this.origin = window.location.origin;
    this.config_beUri = config_beUri;
  }

  newRecordUrl(args) {
    let url = new URL(this.origin + this.config_beUri.pageUriTemplateNewEdit);
    url.searchParams.append('edit[' + args.tableName + '][' + args.pid + ']', 'new');
    if (args.values) {
      for (const propName in args.values) {
        let propValue = args.values[propName];
        url.searchParams.append('defVals[' + args.tableName + '][' + propName + ']', propValue);
      }
    }
    url.searchParams.append('returnUrl', this.config_beUri.pageUriModule);
    return url.toString();
  }

  editRecordUrl(args) {
    let url = new URL(this.origin + this.config_beUri.pageUriTemplateNewEdit);
    url.searchParams.append('edit[' + args.tableName + '][' + args.uid + ']', 'edit');
    url.searchParams.append('returnUrl', this.config_beUri.pageUriModule);
    return url.toString();
  }

}
