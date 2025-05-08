<?php

namespace App\Controller;

use App\Entity\Supplier;
use App\Form\SupplierType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
}