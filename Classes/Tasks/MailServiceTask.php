<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Tasks;

/**
 * Kept so existing scheduler records still unserialize after the queue
 * moved to mailjournal. New tasks must use Mailjournal\MailServiceTask.
 */
class MailServiceTask extends \Quicko\Mailjournal\Tasks\MailServiceTask
{
}
