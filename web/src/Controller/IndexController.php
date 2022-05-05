<?php

namespace App\Controller;

use App\Entity\CrawlSettings;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $entityManager = $doctrine->getManager();

        $crawlersSettings = $doctrine->getRepository(CrawlSettings::class)->findAll(['user_id' => $user->getId()]);

        return $this->render('index/index.html.twig', [
            'crawlersSettings' => $crawlersSettings,
        ]);
    }
}
