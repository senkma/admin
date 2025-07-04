<?php

namespace App\Command;

use App\Entity\Communication;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-communication-emails',
    description: 'Odešle všechny připravené komunikační emaily',
)]
class SendCommunicationEmailsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private EmailService $emailService;

    public function __construct(EntityManagerInterface $entityManager, EmailService $emailService)
    {
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('user-id', null, InputOption::VALUE_OPTIONAL, 'ID uživatele pro filtrování komunikací')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Maximální počet emailů k odeslání', 50)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Pouze zobrazí co by se odeslalo, ale neodešle')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $userId = $input->getOption('user-id');
        $limit = (int) $input->getOption('limit');
        $dryRun = $input->getOption('dry-run');

        $io->title('Odesílání komunikačních emailů');

        if ($dryRun) {
            $io->note('Spuštěno v režimu dry-run - emaily nebudou skutečně odeslány');
        }

        // Najít připravené komunikace
        $queryBuilder = $this->entityManager->getRepository(Communication::class)
            ->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', 'pripraveno')
            ->orderBy('c.createdAt', 'ASC');

        if ($userId) {
            $queryBuilder->andWhere('c.user = :userId')
                ->setParameter('userId', $userId);
        }

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        $communications = $queryBuilder->getQuery()->getResult();

        if (empty($communications)) {
            $io->success('Žádné připravené komunikace k odeslání nebyly nalezeny.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Nalezeno %d připravených komunikací k odeslání', count($communications)));

        $sent = 0;
        $failed = 0;
        $progressBar = $io->createProgressBar(count($communications));
        $progressBar->start();

        foreach ($communications as $communication) {
            $progressBar->advance();

            if ($dryRun) {
                $io->writeln(sprintf(
                    'Dry-run: Komunikace #%d pro %s (Uživatel: %s)',
                    $communication->getId(),
                    $communication->getEmail(),
                    $communication->getUser()->getEmail()
                ));
                continue;
            }

            try {
                $success = $this->emailService->sendCommunicationEmail($communication);
                
                if ($success) {
                    $sent++;
                    $io->writeln(sprintf(
                        ' ✓ Komunikace #%d odeslána na %s',
                        $communication->getId(),
                        $communication->getEmail()
                    ));
                } else {
                    $failed++;
                    $io->writeln(sprintf(
                        ' ✗ Chyba při odesílání komunikace #%d: %s',
                        $communication->getId(),
                        $communication->getErrorMessage() ?: 'Neznámá chyba'
                    ));
                }
            } catch (\Exception $e) {
                $failed++;
                $communication->setStatus('zruseno');
                $communication->setErrorMessage($e->getMessage());
                
                $io->writeln(sprintf(
                    ' ✗ Výjimka při odesílání komunikace #%d: %s',
                    $communication->getId(),
                    $e->getMessage()
                ));
            }

            // Uložit změny po každé komunikaci
            $this->entityManager->flush();
        }

        $progressBar->finish();
        $io->newLine(2);

        if ($dryRun) {
            $io->success(sprintf('Dry-run dokončen. Bylo by odesláno %d komunikací.', count($communications)));
        } else {
            $this->entityManager->flush();

            $io->success(sprintf(
                'Odesílání dokončeno. Úspěšně odesláno: %d, Chyby: %d',
                $sent,
                $failed
            ));

            if ($failed > 0) {
                $io->warning('Některé emaily se nepodařilo odeslat. Zkontrolujte logy pro více informací.');
            }
        }

        return Command::SUCCESS;
    }
}
