<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SystemController extends AbstractController
{
    #[Route('/system', name: 'system_dashboard')]
    public function index(): Response
    {
        return $this->render('system/dashboard.html.twig', [
            'title' => 'Systémová administrace',
        ]);
    }
}