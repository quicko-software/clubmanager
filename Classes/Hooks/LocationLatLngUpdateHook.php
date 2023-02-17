<?php
namespace Quicko\Clubmanager\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LocationLatLngUpdateHook
{
    /**
     * @return DataHandler
     */
    private function getDataHandler()
    {
        return GeneralUtility::makeInstance(DataHandler::class);
    }


    private function updateLatLng($uid, $lat,$lng)
    {
        $updateCommand = [];
        $updateCommand['tx_clubmanager_domain_model_location'][$uid]['latitude'] =  $lat;
        $updateCommand['tx_clubmanager_domain_model_location'][$uid]['longitude'] = $lng;

        $dataHandler = $this->getDataHandler();
        $dataHandler->start($updateCommand, []);
        $commandResult = $dataHandler->process_datamap();
        /*if ($commandResult === false) {
            $this->logger->error(
                sprintf(
                    'Failed to process datamap for command on tx_clubmanager_domain_model_location (%d)',
                    $uid,
                    ['command' => $updateCommand]
                )
            );
       }*/
    }

    protected function getCoordinates(string $address)
    {
        $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($address) . '&format=json&limit=1';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Clubmanager');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode < 300 && $response) {
            $json = json_decode($response, true);
            if (count( $json) > 0 && $json[0]) {
                return $json[0];
            }
        }
    
        return null;
    }

    /**
     * @param string      $status
     * @param string      $table
     * @param int         $id
     * @param array       $fieldArray
     * @param DataHandler $pObj
     */
    public function processDatamap_afterDatabaseOperations(&$status, &$table, &$id, &$fieldArray, &$pObj)
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
        $lat = $record['latitude'];
        $lng = $record['longitude'];

        if (empty($lat) || empty($lng)) {
            $zip = $record["zip"];
            $city = $record["city"];
            $street = $record["street"];
            $loc = $this->getCoordinates("$zip $city, $street");
            if($loc) {
                $this->updateLatLng($uid,$loc["lat"],$loc["lon"]);
            } 
        }
    }
}
