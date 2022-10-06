<?php


// src/Controller/IndexController.php

namespace App\Controller;

use App\Entity\CrawlSettings;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $crawlers = $doctrine->getRepository(CrawlSettings::class)->findAllByUserId($user->getId());

        return $this->render('index/index.html.twig', [
            'crawlers' => $crawlers,
        ]);
    }
}
