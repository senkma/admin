<?php

namespace App\Controller;

use App\Entity\Supplier;
use App\Entity\BankAccount;
use App\Form\SupplierType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SupplierController extends AbstractController
{
    #[Route('/suppliers', name: 'supplier_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $suppliers = $entityManager->getRepository(Supplier::class)->findAll();

        return $this->render('system/supplier/index.html.twig', [
            'suppliers' => $suppliers,
        ]);
    }

    #[Route('/suppliers/create', name: 'supplier_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $supplier = new Supplier();
        $form = $this->createForm(SupplierType::class, $supplier);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $supplier->setUser($this->getUser()); // Nastavení aktuálního uživatele
            $entityManager->persist($supplier);
            $entityManager->flush();

            return $this->redirectToRoute('supplier_index');
        }

        return $this->render('system/supplier/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/suppliers/{id}/edit', name: 'supplier_edit')]
    public function edit(Request $request, Supplier $supplier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SupplierType::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('supplier_index');
        }

        return $this->render('system/supplier/edit.html.twig', [
            'form' => $form->createView(),
            'supplier' => $supplier,
        ]);
    }

    #[Route('/suppliers/{id}/delete', name: 'supplier_delete', methods: ['POST'])]
    public function delete(Supplier $supplier, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($supplier);
        $entityManager->flush();

        return $this->redirectToRoute('supplier_index');
    }

    #[Route('/suppliers/{id}/bank-accounts/create', name: 'supplier_bank_account_create', methods: ['POST'])]
    public function createBankAccount(Request $request, Supplier $supplier, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Debug log
        error_log('Creating bank account with data: ' . json_encode($data));

        try {
            $bankAccount = new BankAccount();
            $bankAccount->setSupplier($supplier);
            $bankAccount->setAccountNumber($data['account_number']);
            $bankAccount->setBankCode($data['bank_code'] ?? null);
            $bankAccount->setBankName($data['bank_name'] ?? null);
            $bankAccount->setIban($data['iban'] ?? null);
            $bankAccount->setSwift($data['swift'] ?? null);
            $bankAccount->setIsDefault((bool)($data['is_default'] ?? false));

            // Pokud je tento účet nastaven jako výchozí, zrušit výchozí u ostatních
            if ($bankAccount->isDefault()) {
                $this->unsetDefaultBankAccounts($supplier, $entityManager);
            }

            $entityManager->persist($bankAccount);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'account' => [
                    'id' => $bankAccount->getId(),
                    'account_number' => $bankAccount->getAccountNumber(),
                    'bank_code' => $bankAccount->getBankCode(),
                    'bank_name' => $bankAccount->getBankName(),
                    'iban' => $bankAccount->getIban(),
                    'swift' => $bankAccount->getSwift(),
                    'is_default' => $bankAccount->isDefault()
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba při vytváření bankovního účtu: ' . $e->getMessage()
            ], 400);
        }
    }

    #[Route('/suppliers/{supplierId}/bank-accounts/{accountId}/update', name: 'supplier_bank_account_update', methods: ['POST'])]
    public function updateBankAccount(Request $request, int $supplierId, int $accountId, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Debug log
        error_log('Updating bank account with data: ' . json_encode($data));

        try {
            $bankAccount = $entityManager->getRepository(BankAccount::class)->find($accountId);
            if (!$bankAccount || $bankAccount->getSupplier()->getId() !== $supplierId) {
                return new JsonResponse(['success' => false, 'message' => 'Bankovní účet nenalezen'], 404);
            }

            $bankAccount->setAccountNumber($data['account_number']);
            $bankAccount->setBankCode($data['bank_code'] ?? null);
            $bankAccount->setBankName($data['bank_name'] ?? null);
            $bankAccount->setIban($data['iban'] ?? null);
            $bankAccount->setSwift($data['swift'] ?? null);
            $bankAccount->setIsDefault((bool)($data['is_default'] ?? false));

            // Pokud je tento účet nastaven jako výchozí, zrušit výchozí u ostatních
            if ($bankAccount->isDefault()) {
                $this->unsetDefaultBankAccounts($bankAccount->getSupplier(), $entityManager, $bankAccount);
            }

            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'account' => [
                    'id' => $bankAccount->getId(),
                    'account_number' => $bankAccount->getAccountNumber(),
                    'bank_code' => $bankAccount->getBankCode(),
                    'bank_name' => $bankAccount->getBankName(),
                    'iban' => $bankAccount->getIban(),
                    'swift' => $bankAccount->getSwift(),
                    'is_default' => $bankAccount->isDefault()
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba při aktualizaci bankovního účtu: ' . $e->getMessage()
            ], 400);
        }
    }

    #[Route('/suppliers/{supplierId}/bank-accounts/{accountId}/delete', name: 'supplier_bank_account_delete', methods: ['POST'])]
    public function deleteBankAccount(int $supplierId, int $accountId, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $bankAccount = $entityManager->getRepository(BankAccount::class)->find($accountId);
            if (!$bankAccount || $bankAccount->getSupplier()->getId() !== $supplierId) {
                return new JsonResponse(['success' => false, 'message' => 'Bankovní účet nenalezen'], 404);
            }

            $entityManager->remove($bankAccount);
            $entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba při mazání bankovního účtu: ' . $e->getMessage()
            ], 400);
        }
    }

    private function unsetDefaultBankAccounts(Supplier $supplier, EntityManagerInterface $entityManager, BankAccount $excludeAccount = null): void
    {
        $bankAccounts = $supplier->getBankAccounts();
        foreach ($bankAccounts as $account) {
            if ($excludeAccount && $account->getId() === $excludeAccount->getId()) {
                continue;
            }
            if ($account->isDefault()) {
                $account->setIsDefault(false);
            }
        }
    }
}