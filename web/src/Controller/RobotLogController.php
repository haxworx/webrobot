<?php

namespace App\Controller;

use App\Entity\CrawlLog;
use App\Entity\CrawlSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class RobotLogController extends AbstractController
{
    #[Route('/robot/log', name: 'app_robot_log')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found.'
            );
        }

        $crawlers = $doctrine->getRepository(CrawlSettings::class)->findAllByUserId($user->getId());
#        $scanDates = $doctrine->getRepository(CrawlLog::class)->findAllScanDatesByUserId($user->getId());
        var_dump($crawlers);

        return $this->render('robot_log/index.html.twig', [
            'controller_name' => 'RobotLogController',
        ]);
    }
}
