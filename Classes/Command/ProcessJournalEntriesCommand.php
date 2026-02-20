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

        $io->title('Journal-Einträge verarbeiten');

        if ($dryRun) {
            $io->note('DRY-RUN Modus - Keine Änderungen werden gespeichert');
        }

        try {
            $now = new \DateTime('now');
            $pendingEntries = $this->journalRepository->findPendingUntilDate($now);
            $pendingCount = 0;
            foreach ($pendingEntries as $entry) {
                $pendingCount++;
            }

            if ($pendingCount === 0) {
                $io->success('Keine fälligen Journal-Einträge gefunden.');
                return Command::SUCCESS;
            }

            $io->text(sprintf('Gefundene fällige Einträge: %d', $pendingCount));

            if ($dryRun) {
                $io->success(sprintf('%d Einträge würden verarbeitet werden.', $pendingCount));
                return Command::SUCCESS;
            }

            // Verarbeite die Einträge
            $processedCount = $this->journalService->processPendingEntries($now);

            $io->success(sprintf('Erfolgreich %d Journal-Einträge verarbeitet.', $processedCount));
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error([
                'Fehler beim Verarbeiten der Journal-Einträge:',
                $e->getMessage(),
                '',
                'Stack Trace:',
                $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}
