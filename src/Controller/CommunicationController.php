<?php

namespace App\Controller;

use App\Entity\Communication;
use App\Form\CommunicationType;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/communications')]
class CommunicationController extends AbstractController
{
    #[Route('/', name: 'communication_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $communications = $entityManager->getRepository(Communication::class)->findBy([
            'user' => $this->getUser()
        ], ['createdAt' => 'DESC']);

        return $this->render('system/communication/index.html.twig', [
            'communications' => $communications,
        ]);
    }

    #[Route('/new', name: 'communication_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $communication = new Communication();
        $communication->setUser($this->getUser());

        $form = $this->createForm(CommunicationType::class, $communication, [
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($communication);
            $entityManager->flush();

            $this->addFlash('success', 'Komunikace byla úspěšně vytvořena.');
            return $this->redirectToRoute('communication_index');
        }

        return $this->render('system/communication/create.html.twig', [
            'communication' => $communication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'communication_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Communication $communication): Response
    {
        $this->denyAccessUnlessGranted('view', $communication);

        return $this->render('system/communication/show.html.twig', [
            'communication' => $communication,
        ]);
    }

    #[Route('/{id}/edit', name: 'communication_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Communication $communication, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $communication);

        $form = $this->createForm(CommunicationType::class, $communication, [
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Komunikace byla úspěšně upravena.');
            return $this->redirectToRoute('communication_index');
        }

        return $this->render('system/communication/edit.html.twig', [
            'communication' => $communication,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'communication_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Communication $communication, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('delete', $communication);

        if ($this->isCsrfTokenValid('delete'.$communication->getId(), $request->request->get('_token'))) {
            $entityManager->remove($communication);
            $entityManager->flush();
            $this->addFlash('success', 'Komunikace byla úspěšně smazána.');
        }

        return $this->redirectToRoute('communication_index');
    }

    #[Route('/{id}/execute', name: 'communication_execute', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function execute(Communication $communication, EmailService $emailService, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', $communication);

        try {
            // Zkontrolovat, jestli už není vykonáno
            if ($communication->getStatus() === 'vykonano') {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Komunikace již byla vykonána.'
                ]);
            }

            // Odeslat email
            $success = $emailService->sendCommunicationEmail($communication);

            if ($success) {
                $entityManager->flush();

                return new JsonResponse([
                    'success' => true,
                    'message' => 'Email byl úspěšně odeslán.'
                ]);
            } else {
                $entityManager->flush();

                return new JsonResponse([
                    'success' => false,
                    'message' => 'Chyba při odesílání emailu: ' . $communication->getErrorMessage()
                ]);
            }

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba při odesílání emailu: ' . $e->getMessage()
            ]);
        }
    }

    #[Route('/send-daily', name: 'communication_send_daily', methods: ['POST'])]
    public function sendDaily(EmailService $emailService, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $communications = $entityManager->getRepository(Communication::class)->findBy([
                'user' => $this->getUser(),
                'status' => 'pripraveno'
            ]);

            $sent = 0;
            $failed = 0;

            foreach ($communications as $communication) {
                $success = $emailService->sendCommunicationEmail($communication);
                if ($success) {
                    $sent++;
                } else {
                    $failed++;
                }
            }

            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => "Odesláno: {$sent}, Chyby: {$failed}"
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba při hromadném odesílání: ' . $e->getMessage()
            ]);
        }
    }
}
