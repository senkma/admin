<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Supplier;
use App\Form\InvoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{

    #[Route('/invoices', name: 'invoice_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Získání unikátních roků z faktur
        $suppliers = $user->getSuppliers();
        $years = $entityManager->createQueryBuilder()
            ->select('DISTINCT YEAR(i.date_created) AS year')
            ->from(Invoice::class, 'i')
            ->where('i.supplier IN (:suppliers)')
            ->setParameter('suppliers', $suppliers)
            ->orderBy('year', 'DESC')
            ->getQuery()
            ->getResult();
        $years = array_column($years, 'year');

        // Filtrování faktur podle roku (pokud je vybrán)
        $selectedYear = $request->query->get('year');
        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('i')
            ->from(Invoice::class, 'i')
            ->where('i.supplier IN (:suppliers)')
            ->setParameter('suppliers', $user->getSuppliers());

        if ($selectedYear) {
            $queryBuilder->andWhere('YEAR(i.date_created) = :year')
                ->setParameter('year', $selectedYear);
        }

        $invoices = $queryBuilder->getQuery()->getResult();

        return $this->render('system/invoice/index.html.twig', [
            'invoices' => $invoices,
            'years' => $years,
            'currentYear' => $selectedYear,
        ]);
    }

    #[Route('/invoices/create', name: 'invoice_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $currentYear = (new \DateTime())->format('Y');

        // Získání posledního čísla faktury uživatele
        $lastInvoice = $entityManager->createQueryBuilder()
            ->select('i')
            ->from(Invoice::class, 'i')
            ->where('i.supplier IN (:suppliers)')
            ->setParameter('suppliers', $user->getSuppliers())
            ->andWhere('i.invoice_number LIKE :yearPrefix')
            ->setParameter('yearPrefix', $currentYear . '%')
            ->orderBy('i.invoice_number', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // Generování nového čísla faktury
        $newInvoiceNumber = $currentYear . str_pad(
                $lastInvoice ? (int)substr($lastInvoice->getInvoiceNumber(), 4) + 1 : 1,
                4,
                '0',
                STR_PAD_LEFT
            );

        $invoice = new Invoice();
        $invoice->setInvoiceNumber($newInvoiceNumber);
        $invoice->setDateCreated(new \DateTime()); // Nastavení aktuálního data
        $invoice->setDateDue((new \DateTime())->modify('+14 days')); // Nastavení data splatnosti o 14 dní později

        $form = $this->createForm(InvoiceType::class, $invoice, ['user' => $this->getUser()]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('invoice_index');
        }

        return $this->render('system/invoice/create.html.twig', [
            'form' => $form->createView(),
            'invoice' => $invoice,
        ]);
    }

    #[Route('/invoices/{id}/edit', name: 'invoice_edit')]
    public function edit(Request $request, Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvoiceType::class, $invoice, ['user' => $this->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('invoice_index');
        }

        return $this->render('system/invoice/edit.html.twig', [
            'form' => $form->createView(),
            'invoice' => $invoice,
        ]);
    }

    #[Route('/invoices/{id}/delete', name: 'invoice_delete', methods: ['POST'])]
    public function delete(Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($invoice);
        $entityManager->flush();

        return $this->redirectToRoute('invoice_index');
    }

    #[Route('/invoices/{id}/export-pdf', name: 'invoice_export_pdf')]
    public function exportPdf(Invoice $invoice): Response
    {
        $html = $this->renderView('system/invoice/pdf.html.twig', [
            'invoice' => $invoice,
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice_' . $invoice->getInvoiceNumber() . '.pdf"',
        ]);
    }

    #[Route('/api/suppliers/{id}/bank-accounts', name: 'api_supplier_bank_accounts', methods: ['GET'])]
    public function getSupplierBankAccounts(Supplier $supplier): JsonResponse
    {
        $bankAccounts = [];
        foreach ($supplier->getBankAccounts() as $bankAccount) {
            $bankAccounts[] = [
                'id' => $bankAccount->getId(),
                'value' => $bankAccount->getId(),
                'label' => $bankAccount->getFullAccountNumber() .
                          ($bankAccount->getBankName() ? ' (' . $bankAccount->getBankName() . ')' : '') .
                          ($bankAccount->isDefault() ? ' - Výchozí' : ''),
                'isDefault' => $bankAccount->isDefault()
            ];
        }

        return new JsonResponse($bankAccounts);
    }
}