<?php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ClientType;

class ClientController extends AbstractController
{
    #[Route('/clients', name: 'client_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Získání aktuálně přihlášeného uživatele
        $clients = $entityManager->getRepository(Client::class)->findBy(['user' => $user]);

        return $this->render('system/client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/clients/create', name: 'client_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client->setUser($this->getUser()); // Nastavení aktuálního uživatele
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('client_index');
        }

        return $this->render('system/client/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/clients/{id}/edit', name: 'client_edit')]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('client_index');
        }

        return $this->render('system/client/edit.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }

    #[Route('system/clients/{id}/delete', name: 'client_delete', methods: ['POST'])]
    public function delete(Client $client, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($client);
        $entityManager->flush();

        return $this->redirectToRoute('client_index');
    }

    #[Route('/api/clients/{id}/due-days', name: 'api_client_due_days', methods: ['GET'])]
    public function getClientDueDays(Client $client): JsonResponse
    {
        // Zkontrolovat, jestli klient patří aktuálnímu uživateli
        if ($client->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Nemáte oprávnění zobrazit údaje tohoto klienta.');
        }

        return new JsonResponse([
            'dueDays' => $client->getDueDays()
        ]);
    }
}