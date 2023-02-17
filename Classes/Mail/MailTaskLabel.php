<?php

namespace Quicko\Clubmanager\Mail;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use Quicko\Clubmanager\Domain\Model\Mail\Task;
use Quicko\Clubmanager\Mail\Generator\MailGeneratorFactory;
use Quicko\Clubmanager\Mail\Generator\Arguments\MailGeneratorArgumentsSerializer;

class MailTaskLabel
{
   public function generateMailTaskTitle(&$parameters)
   {
      $record = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
      $newTitle = "!!! INVALID !!!";
      try {
         if ($record != null) {

            $instance = MailGeneratorFactory::createGenerator($record['generator_class']);
            $baseMailGeneratorArguments = MailGeneratorArgumentsSerializer::deserialize($record['generator_arguments']);

            $newTitle = $instance->getLabel($baseMailGeneratorArguments) . " (" . $instance->getMailTo($baseMailGeneratorArguments) . ")";
            $done = $record['send_state'] == Task::SEND_STATE_DONE;
            if ($done) {
               $newTitle = "DONE : " . $newTitle;
            } else {
               $hasErrors = $record['processed_time'] > 0;
               if ($hasErrors) {
                  $newTitle = "ERROR : " . $newTitle;
               }
            }
         }
      } catch (\InvalidArgumentException $e) {
      }
      $parameters['title'] = $newTitle;
   }
}
