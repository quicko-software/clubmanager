
import BeUrlBuilder from './BeUrlBuilder';

/**
  Usage:
  var markup = new BeToolboxBuilder(config_beUri)
    .setRecord('tx_myext_...', 12)
    .addTool('edit') // 'edit' or 'delete'
    .setText(row.label)
  .makeToolboxMarkup();
*/
export default class BeToolboxBuilder {

  constructor(config_beUri) {
    this.config_beUri = config_beUri;

    this.tools = [];
    this.text = '';
    this.uid = 0;
    this.tableName = '';
  }

  addTool(toolName) {
    this.tools.push(toolName);
    return this;
  }
  setText(text) {
    this.text = text;
    return this;
  }
  setRecord(tableName, uid) {
    this.tableName = tableName;
    this.uid = uid;
    return this;
  }
  makeToolboxMarkup() {
    let markup = '';
    markup += '<div class="bem_toolbox">';
    for (let i = 0; i < this.tools.length; ++i) {
      let tool = this.tools[i];
      let title = TYPO3.lang["bem-toolbox." + tool];
      if(this.uid != 0) {
        title += " (id=" + this.uid + ")";
      }
      markup += '<a';
      markup += ' class="bem_t3link ' + this.#getLinkClass(tool) + '" ';
      markup += ' href="' + this.#getHref(tool) + '" ';
      markup += ' title="' + title + '" ';
      if(tool == "edit") {
        markup += ' onclick="event.stopPropagation();"';
      }
      markup += ''
      markup += '>';
      markup += '<i class="bi ' + this.#getIconClass(tool) + '"></i>'
      markup += '</a>';
    }
    markup += '<span class="bem_toolbox-label">' + this.text + '</span>';
    markup += '</div>';
    return markup;
  }

  #getLinkClass(tool) {
    return tool + '_link';
  }
  #getIconClass(tool) {
    switch (tool) {
      case 'edit': return 'bi-pencil-fill';
      case 'delete': return 'bi-trash-fill';
      default: return '';
    }
  }
  #getHref(tool) {
    if (tool === 'edit') {
      if (!this.tableName || !this.uid) return "#";
      let hrefEdit = new BeUrlBuilder(this.config_beUri)
        .editRecordUrl({
          tableName: this.tableName,
          uid: this.uid
        }
        );
      return hrefEdit;
    }
    if (tool === 'delete') {
      return '#';
    }
  }
}
