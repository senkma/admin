<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\Supplier;
use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Communication;
use App\Form\ServiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/services')]
class ServiceController extends AbstractController
{
    #[Route('/', name: 'service_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $services = $entityManager->getRepository(Service::class)->findBy([
            'user' => $this->getUser()
        ], ['createdAt' => 'DESC']);

        return $this->render('system/service/index.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/new', name: 'service_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $service->setUser($this->getUser());

        $form = $this->createForm(ServiceType::class, $service, [
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->setUpdatedAt(new \DateTime());
            $entityManager->persist($service);
            $entityManager->flush();

            $this->addFlash('success', 'Služba byla úspěšně vytvořena.');
            return $this->redirectToRoute('service_index');
        }

        return $this->render('system/service/create.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'service_show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        $this->denyAccessUnlessGranted('view', $service);

        return $this->render('system/service/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/{id}/edit', name: 'service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $service);

        $form = $this->createForm(ServiceType::class, $service, [
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Služba byla úspěšně aktualizována.');
            return $this->redirectToRoute('service_index');
        }

        return $this->render('system/service/edit.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'service_delete', methods: ['POST'])]
    public function delete(Service $service, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('delete', $service);

        $entityManager->remove($service);
        $entityManager->flush();

        $this->addFlash('success', 'Služba byla úspěšně smazána.');
        return $this->redirectToRoute('service_index');
    }

    #[Route('/{id}/toggle-active', name: 'service_toggle_active', methods: ['POST'])]
    public function toggleActive(Service $service, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $service);

        $service->setIsActive(!$service->isActive());
        $service->setUpdatedAt(new \DateTime());
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'isActive' => $service->isActive(),
            'message' => $service->isActive() ? 'Služba byla aktivována.' : 'Služba byla deaktivována.'
        ]);
    }

    #[Route('/{id}/execute', name: 'service_execute', methods: ['POST'])]
    public function executeService(Service $service, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $service);

        try {
            // Zkontrolovat, jestli je služba aktivní
            if (!$service->isActive()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Služba není aktivní a nelze ji vyvolat.'
                ]);
            }

            // Zkontrolovat, jestli má služba položky
            if ($service->getItems()->isEmpty()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Služba nemá definované žádné položky.'
                ]);
            }

            // Vytvořit fakturu ze služby
            $currentDate = new \DateTime();
            $invoice = $this->createInvoiceFromService($service, $currentDate, $entityManager);

            $entityManager->persist($invoice);

            // Aktualizovat datum poslední faktury
            $service->setLastInvoiceDate($currentDate);
            $service->setUpdatedAt(new \DateTime());

            // Pokud má služba zaškrtnuté odesílání emailu, vytvořit komunikaci
            if ($service->getSendEmail()) {
                $this->createCommunicationForService($service, $invoice, $entityManager);
            }

            $entityManager->flush();

            $message = 'Služba byla úspěšně vyvolána. Faktura č. ' . $invoice->getInvoiceNumber() . ' byla vytvořena.';
            if ($service->getSendEmail()) {
                $message .= ' Komunikace pro odeslání emailu byla také vytvořena.';
            }

            return new JsonResponse([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba při vyvolání služby: ' . $e->getMessage()
            ]);
        }
    }

    #[Route('/client/{id}/services', name: 'client_services', methods: ['GET'])]
    public function clientServices(Client $client, EntityManagerInterface $entityManager): Response
    {
        // Zkontrolovat, jestli klient patří aktuálnímu uživateli
        if ($client->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Nemáte oprávnění zobrazit služby tohoto klienta.');
        }

        $services = $entityManager->getRepository(Service::class)->findBy([
            'user' => $this->getUser(),
            'client' => $client
        ], ['createdAt' => 'DESC']);

        return $this->render('system/service/client_services.html.twig', [
            'services' => $services,
            'client' => $client,
        ]);
    }





    private function createInvoiceFromService(Service $service, \DateTime $invoiceDate, EntityManagerInterface $entityManager): Invoice
    {
        $invoice = new Invoice();

        // Základní údaje
        $invoice->setSupplier($service->getSupplier());
        $invoice->setClient($service->getClient());
        $invoice->setBankAccount($service->getBankAccount());

        // Datumy
        $invoice->setDateCreated($invoiceDate);
        $invoice->setDateDue($service->calculateDueDate($invoiceDate));

        // Generovat číslo faktury
        $invoiceNumber = $this->generateInvoiceNumber($service->getUser(), $invoiceDate, $entityManager);
        $invoice->setInvoiceNumber($invoiceNumber);

        // Přidat položky
        foreach ($service->getItems() as $serviceItem) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->setInvoice($invoice);
            $invoiceItem->setName($serviceItem->getDescription());
            $invoiceItem->setQuantity((int)$serviceItem->getQuantity());
            $invoiceItem->setPricePerUnit($serviceItem->getUnitPrice());

            $invoice->addItem($invoiceItem);
        }

        return $invoice;
    }

    private function generateInvoiceNumber($user, \DateTime $date, EntityManagerInterface $entityManager): string
    {
        // Najít nejvyšší číslo faktury pro daný rok
        $year = $date->format('Y');

        $qb = $entityManager->createQueryBuilder();
        $qb->select('i.invoice_number')
           ->from(Invoice::class, 'i')
           ->where('i.supplier IN (:suppliers)')
           ->setParameter('suppliers', $user->getSuppliers())
           ->andWhere('i.invoice_number LIKE :yearPrefix')
           ->setParameter('yearPrefix', $year . '%')
           ->orderBy('i.invoice_number', 'DESC')
           ->setMaxResults(1);

        $lastInvoice = $qb->getQuery()->getOneOrNullResult();

        if ($lastInvoice) {
            // Extrahovat číslo z formátu YYYY-NNNN
            $parts = explode('-', $lastInvoice['invoice_number']);
            $lastNumber = isset($parts[1]) ? (int)$parts[1] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function createCommunicationForService(Service $service, Invoice $invoice, EntityManagerInterface $entityManager): void
    {
        $communication = new Communication();
        $communication->setUser($service->getUser());
        $communication->setSupplier($service->getSupplier());
        $communication->setClient($service->getClient());
        $communication->setService($service);
        $communication->setInvoice($invoice);

        // Určit email příjemce
        $email = $service->getClient()->getInvoiceEmail();
        if (!$email) {
            $email = $service->getSupplier()->getInvoiceEmail();
        }

        if ($email) {
            $communication->setEmail($email);
        } else {
            // Fallback na email uživatele
            $communication->setEmail($service->getUser()->getEmail());
        }

        // Vytvořit zprávu
        $message = "Dobrý den,\n\n";
        $message .= "byla vytvořena nová faktura pro službu: " . $service->getName() . "\n\n";
        $message .= "Číslo faktury: " . $invoice->getInvoiceNumber() . "\n";
        $message .= "Datum vytvoření: " . $invoice->getDateCreated()->format('d.m.Y') . "\n";
        $message .= "Datum splatnosti: " . $invoice->getDateDue()->format('d.m.Y') . "\n\n";
        $message .= "Faktura je připojena jako příloha tohoto emailu.\n\n";
        $message .= "S pozdravem,\n";
        $message .= "Fakturační systém";

        $communication->setMessage($message);
        $communication->setStatus('pripraveno');

        $entityManager->persist($communication);
    }
}
