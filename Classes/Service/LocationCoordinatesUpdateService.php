<?php
declare(strict_types=1);
namespace Quicko\Clubmanager\Service;

defined('TYPO3') or exit;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;



class LocationCoordinatesUpdateService
{

    protected function buildSearchAddress(array $record): string
    {
        $zip = $record["zip"] ?? '';
        $city = $record["city"] ?? '';
        $street = $record["street"] ?? '';
        return "$zip $city, $street";
    }

    /**
     * Summary of needsUpdate
     * @param array $record
     * @param array<string, mixed> $changedFields
     * @return bool
     */
    public function needsUpdate(array $record, ?array $changedFields = null): bool
    {
        if (empty($record['latitude']) || empty($record['longitude'])) {
            return true;
        }
        
        if($changedFields) {
            /**
             * latitude,longitude wurden vom Nutzer geändert, daher keine Update notwendig
             */
            foreach($changedFields as $field => $value) {
                if(in_array($field, ['latitude', 'longitude'])) {
                    return false;
                }
            }
            foreach($changedFields as $field => $value) {
                if(in_array($field, ['zip', 'city', 'street'])) {
                    return true;
                }
            }
        }
        return false;
    }

    public function updateCoordinates(int $uid): void
    {
        $record = BackendUtility::getRecord(
            'tx_clubmanager_domain_model_location',
            $uid,
            '*',
        );
        if (empty($record)) {
            return;
        }
        $address = $this->buildSearchAddress($record);
        $coordinates = $this->getCoordinates($address);
        if (empty($coordinates)) {
            return;
        }

        $record['latitude'] = $coordinates['lat'];
        $record['longitude'] = $coordinates['lon'];
        $this->updateRecord($uid, $record);
    }

    protected function updateRecord(int $uid, array $record): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_clubmanager_domain_model_location');
        $connection->update('tx_clubmanager_domain_model_location', $record, ['uid' => $uid]);
    }

    protected function getCoordinates(string $address): mixed
    {
        $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($address) . '&format=json&limit=1';

        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);

        try {
            $response = $requestFactory->request($url, 'GET', [
                'headers' => [
                    'User-Agent' => 'Clubmanager',
                    'Accept' => 'application/json',
                ],
                'timeout' => 3,
            ]);
        } catch (\Throwable $e) {
            return null;
        }

        if ($response->getStatusCode() >= 300) {
            return null;
        }

        $body = (string) $response->getBody();
        if ($body === '') {
            return null;
        }

        $json = json_decode($body, true);
        if (is_array($json) && count($json) > 0 && !empty($json[0])) {
            return $json[0];
        }

        return null;
    }
}