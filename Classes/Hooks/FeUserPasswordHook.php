<?php

namespace Quicko\Clubmanager\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use Quicko\Clubmanager\Domain\Model\Mail\Task;
use Quicko\Clubmanager\Mail\MailQueue;
use Quicko\Clubmanager\Mail\Generator\PasswordRecoveryGenerator;
use Quicko\Clubmanager\Mail\Generator\Arguments\PasswordRecoveryArguments;

class FeUserPasswordHook
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger|null $logger
     */
    public function __construct(Logger $logger = null)
    {
        if ($logger === null) {
            /** @var LogManager $logManager */
            $logManager = GeneralUtility::makeInstance(LogManager::class);
            $this->logger = $logManager->getLogger(__CLASS__);
        } else {
            $this->logger = $logger;
        }
    }

    /**
     * @return DataHandler
     */
    private function getDataHandler()
    {
        return GeneralUtility::makeInstance(DataHandler::class);
    }

    private function randomPassword($length)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890.-#!$';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    private function generatePassword($uid)
    {
        $password = $this->randomPassword(8);
        $hashInstance = GeneralUtility::makeInstance(PasswordHashFactory::class)->getDefaultHashInstance('FE');
        $hashedPassword = $hashInstance->getHashedPassword($password);

        $updatePasswordCommand = [];
        $updatePasswordCommand['fe_users'][$uid]['password'] = $hashedPassword;
        $dataHandler = $this->getDataHandler();
        $dataHandler->start($updatePasswordCommand, []);
        $commandResult = $dataHandler->process_datamap();
        if ($commandResult === false) {
            $this->logger->error(
                sprintf(
                    'Failed to process datamap for command on fe_user (%d)',
                    $uid,
                    ['command' => $updatePasswordCommand]
                )
            );
        }
        return $password;
    }


    private function getMemberJoinedMainLocation($memberUid)
    {
        $queryBuilder = GeneralUtility::makeInstance(
            ConnectionPool::class
        )->getQueryBuilderForTable('tx_clubmanager_domain_model_location');

        $queryBuilder->getRestrictions()->removeAll();
        $row = $queryBuilder
            ->select('tx_clubmanager_domain_model_location.*')
            ->from('tx_clubmanager_domain_model_location')
            ->join(
                'tx_clubmanager_domain_model_location',
                'tx_clubmanager_domain_model_member',
                'member',
                'member.uid = tx_clubmanager_domain_model_location.member'
            )
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        'tx_clubmanager_domain_model_location.kind',
                        $queryBuilder->createNamedParameter((int) 0, \PDO::PARAM_INT) // 0 == main location
                    ),
                    $queryBuilder->expr()->eq(
                        'member.uid',
                        $queryBuilder->createNamedParameter($memberUid, \PDO::PARAM_INT)
                    ),
                )
            )
            ->execute();
        if ($row) {
            return $row;
        }
    }


    public function processDatamap_afterAllOperations(\TYPO3\CMS\Core\DataHandling\DataHandler &$pObj)
    {
        if (!array_key_exists('fe_users', $pObj->datamap)) {
            return;
        }
        foreach ($pObj->datamap['fe_users'] as $uid => $propertyMap) {
            if (!array_key_exists('password', $propertyMap)) { // changed some other value(s)
                continue;
            }
            if (str_starts_with($uid, 'NEW')) {
                if(array_key_exists($uid,$pObj->substNEWwithIDs)) {
                    $newUid = $pObj->substNEWwithIDs[$uid];
                } else {
                    continue;
                }
            } else {
                $newUid = $uid;
            }

            $record = BackendUtility::getRecord(
                'fe_users',
                $newUid,
                '*',
            );

            $enteredPassword = $propertyMap['password'];

            if (!empty($enteredPassword)) {
                continue; // nur neu, wenn geleert
            }

            $member = $this->getMemberJoinedMainLocation($record['clubmanager_member']);

            if (!$member) { // Kann eigentlich nicht passieren, weil der fe_users-Datensatz nur im Kontext eines Member angelegt werden kann
                continue;
            }

            $newPassword = $this->generatePassword($record['uid']);
            // IST Zustand: Wenn ein leeres Passwort => Neues generieren und Mail an Mitglied
            /** @var PasswordRecoveryArguments $args */
            $args = new PasswordRecoveryArguments();
            $args->memberUid = $record['clubmanager_member'];
            $args->templateName = 'Logindata';
            MailQueue::addMailTask(PasswordRecoveryGenerator::class, $args,Task::PRIORITY_LEVEL_MEDIUM);
            
        }
    }
}
