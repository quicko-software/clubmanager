<?php
namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Service\LocationCoordinatesUpdateService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LocationLatLngUpdateHook
{

    /**
     * @param string      $status
     * @param string      $table
     * @param string         $id
     * @param array       $fieldArray
     * @param DataHandler $pObj
     */
    public function processDatamap_afterDatabaseOperations(string &$status, string &$table, string &$id, array &$fieldArray, DataHandler &$pObj): void
    {
        if ($table !== 'tx_clubmanager_domain_model_location') return;
        if ($status !== 'update' && $status !== 'new') return;
        
        $uid = $id;
        if ($status === 'new') {
            $uid = $pObj->substNEWwithIDs[$id];
        }

        $record = BackendUtility::getRecord(
            'tx_clubmanager_domain_model_location',
            $uid,
            '*',
        );
        if(!$record) return;

        $coordinatesUpdateService = GeneralUtility::makeInstance(LocationCoordinatesUpdateService::class);
        if($coordinatesUpdateService->needsUpdate($record,$fieldArray)) {
            $coordinatesUpdateService->updateCoordinates($uid);
        }
        
    }
}
