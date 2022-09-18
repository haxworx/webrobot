<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnregisteredController extends AbstractController
{
    #[Route('/unregistered', name: 'app_unregistered')]
    public function index(): Response
    {
        return $this->render('unregistered/index.html.twig', [
        ]);
    }
}
