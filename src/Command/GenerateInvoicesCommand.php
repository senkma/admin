<?php

namespace App\Command;

use App\Entity\Service;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-invoices',
    description: 'Automaticky generuje faktury na základě definovaných služeb',
)]
class GenerateInvoicesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Pouze zobrazí co by se stalo, ale nevytvoří faktury')
            ->addOption('force-date', null, InputOption::VALUE_REQUIRED, 'Použije specifické datum místo dnešního (formát: Y-m-d)')
            ->setHelp('Tento příkaz projde všechny aktivní služby a vytvoří faktury pro ty, které splňují kritéria pro automatické fakturování.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = $input->getOption('dry-run');
        $forceDate = $input->getOption('force-date');

        // Určit datum pro kontrolu
        $checkDate = new \DateTime();
        if ($forceDate) {
            try {
                $checkDate = new \DateTime($forceDate);
            } catch (\Exception $e) {
                $io->error('Neplatný formát data. Použijte formát Y-m-d (např. 2024-01-15)');
                return Command::FAILURE;
            }
        }

        $io->title('Automatické generování faktur');
        $io->info('Datum kontroly: ' . $checkDate->format('d.m.Y'));

        if ($isDryRun) {
            $io->warning('REŽIM DRY-RUN - žádné faktury nebudou skutečně vytvořeny');
        }

        // Najít všechny aktivní služby
        $services = $this->entityManager->getRepository(Service::class)->findBy([
            'isActive' => true
        ]);

        $io->info('Nalezeno ' . count($services) . ' aktivních služeb');

        $createdInvoices = 0;
        $skippedServices = 0;

        foreach ($services as $service) {
            $io->section('Kontrola služby: ' . $service->getName());

            if (!$service->shouldCreateInvoice($checkDate)) {
                $io->text('Služba nesplňuje kritéria pro vytvoření faktury');
                $skippedServices++;
                continue;
            }

            $io->success('Služba splňuje kritéria - vytváří se faktura');

            if (!$isDryRun) {
                try {
                    $invoice = $this->createInvoiceFromService($service, $checkDate);
                    $this->entityManager->persist($invoice);
                    
                    // Aktualizovat datum poslední faktury
                    $service->setLastInvoiceDate($checkDate);
                    $service->setUpdatedAt(new \DateTime());
                    
                    $this->entityManager->flush();
                    
                    $io->success('Faktura č. ' . $invoice->getInvoiceNumber() . ' byla vytvořena');
                    $createdInvoices++;
                } catch (\Exception $e) {
                    $io->error('Chyba při vytváření faktury: ' . $e->getMessage());
                }
            } else {
                $io->text('DRY-RUN: Faktura by byla vytvořena');
                $createdInvoices++;
            }
        }

        $io->newLine();
        $io->success('Dokončeno!');
        $io->table(
            ['Statistika', 'Počet'],
            [
                ['Zkontrolované služby', count($services)],
                ['Přeskočené služby', $skippedServices],
                ['Vytvořené faktury', $createdInvoices],
            ]
        );

        return Command::SUCCESS;
    }

    private function createInvoiceFromService(Service $service, \DateTime $invoiceDate): Invoice
    {
        $invoice = new Invoice();
        
        // Základní údaje
        $invoice->setUser($service->getUser());
        $invoice->setSupplier($service->getSupplier());
        $invoice->setClient($service->getClient());
        $invoice->setBankAccount($service->getBankAccount());
        
        // Datumy
        $invoice->setDateCreated($invoiceDate);
        $invoice->setDateDue($service->calculateDueDate($invoiceDate));
        
        // Generovat číslo faktury
        $invoiceNumber = $this->generateInvoiceNumber($service->getUser(), $invoiceDate);
        $invoice->setInvoiceNumber($invoiceNumber);
        
        // Přidat položky
        foreach ($service->getItems() as $serviceItem) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->setInvoice($invoice);
            $invoiceItem->setDescription($serviceItem->getDescription());
            $invoiceItem->setQuantity($serviceItem->getQuantity());
            $invoiceItem->setUnit($serviceItem->getUnit());
            $invoiceItem->setUnitPrice($serviceItem->getUnitPrice());
            $invoiceItem->setVatRate($serviceItem->getVatRate());
            
            $invoice->addItem($invoiceItem);
        }
        
        return $invoice;
    }

    private function generateInvoiceNumber($user, \DateTime $date): string
    {
        // Najít nejvyšší číslo faktury pro daný rok
        $year = $date->format('Y');
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('i.invoiceNumber')
           ->from(Invoice::class, 'i')
           ->where('i.user = :user')
           ->andWhere('YEAR(i.dateCreated) = :year')
           ->setParameter('user', $user)
           ->setParameter('year', $year)
           ->orderBy('i.invoiceNumber', 'DESC')
           ->setMaxResults(1);

        $lastInvoice = $qb->getQuery()->getOneOrNullResult();
        
        if ($lastInvoice) {
            // Extrahovat číslo z formátu YYYY-NNNN
            $parts = explode('-', $lastInvoice['invoiceNumber']);
            $lastNumber = isset($parts[1]) ? (int)$parts[1] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
