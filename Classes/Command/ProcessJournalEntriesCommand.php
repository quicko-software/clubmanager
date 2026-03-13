<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Command;

use Quicko\Clubmanager\Domain\Repository\MemberJournalEntryRepository;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use Quicko\Clubmanager\Service\MemberJournalService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

#[AsCommand(
    name: 'clubmanager:journal:process',
    description: 'Verarbeitet fällige Journal-Einträge (Status- und Level-Änderungen)'
)]
class ProcessJournalEntriesCommand extends Command
{
    public function __construct(
        protected MemberJournalService $journalService,
        protected MemberJournalEntryRepository $journalRepository,
        protected MemberRepository $memberRepository,
        protected PersistenceManager $persistenceManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Nur anzeigen was passieren würde, ohne tatsächliche Änderungen'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $verbose = $output->isVerbose();

        $io->title('Journal-Einträge verarbeiten');

        if ($verbose) {
            $io->text([
                sprintf('EXEC_TIME:       %s (%d)', date('Y-m-d H:i:s', (int) ($GLOBALS['EXEC_TIME'] ?? 0)), (int) ($GLOBALS['EXEC_TIME'] ?? 0)),
                sprintf('SIM_ACCESS_TIME: %s (%d)', date('Y-m-d H:i:s', (int) ($GLOBALS['SIM_ACCESS_TIME'] ?? 0)), (int) ($GLOBALS['SIM_ACCESS_TIME'] ?? 0)),
            ]);
        }

        if ($dryRun) {
            $io->note('DRY-RUN Modus - Keine Änderungen werden gespeichert');
        }

        try {
            $now = new \DateTime('now');
            $pendingEntries = $this->journalRepository->findPendingUntilDate($now);
            $pendingCount = 0;

            $rows = [];
            foreach ($pendingEntries as $entry) {
                $pendingCount++;
                if ($verbose) {
                    $rows[] = [
                        $entry->getUid(),
                        $entry->getMember(),
                        $entry->getEntryType(),
                        $entry->getEffectiveDate()?->format('Y-m-d H:i:s') ?? '-',
                    ];
                }
            }

            if ($pendingCount === 0) {
                $io->success('Keine fälligen Journal-Einträge gefunden.');
                return Command::SUCCESS;
            }

            $io->text(sprintf('Gefundene fällige Einträge: %d', $pendingCount));

            if ($verbose && $rows !== []) {
                $io->table(['Entry UID', 'Member UID', 'Typ', 'Effective Date'], $rows);
            }

            if ($dryRun) {
                $io->success(sprintf('%d Einträge würden verarbeitet werden.', $pendingCount));
                return Command::SUCCESS;
            }

            $processedCount = $this->journalService->processPendingEntries($now);

            $io->success(sprintf('Erfolgreich %d von %d Journal-Einträgen verarbeitet.', $processedCount, $pendingCount));
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error([
                'Fehler beim Verarbeiten der Journal-Einträge:',
                sprintf('[%s] %s', get_class($e), $e->getMessage()),
                '',
                sprintf('EXEC_TIME: %d, SIM_ACCESS_TIME: %d', (int) ($GLOBALS['EXEC_TIME'] ?? 0), (int) ($GLOBALS['SIM_ACCESS_TIME'] ?? 0)),
                '',
                'Stack Trace:',
                $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}
