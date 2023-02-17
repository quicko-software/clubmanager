
export default class RecordDeletionPostBuilder {

  /*
    Build an array for TYPO3 backend 'route/commit'
    for record deletion, for example:
    {
      cmd : {
        tx_tablename : {
          47 : {
            delete : 1
          },
          11 : {
            delete : 1
          }
        }
      }
    }
    which is intended to be transfered to PHP and deserialized
    to an array/map like this:

    cmd['tx_tablename'][47]['delete'] = 1;
    cmd['tx_tablename'][11]['delete'] = 1;

  */
  static build(tableName, uidArrayToDelete)
  {
    let msgData = {};
    msgData['cmd'] = {};
    msgData['cmd'][tableName] = {};

    for (let i = 0; i < uidArrayToDelete.length; ++i) {
      let uid = uidArrayToDelete[i];
      msgData['cmd'][tableName][uid] = {};
      msgData['cmd'][tableName][uid]['delete'] = 1;
    }

    return msgData;
  }
}
