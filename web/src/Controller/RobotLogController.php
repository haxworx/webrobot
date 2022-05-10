<?php

namespace App\Controller;

use App\Entity\CrawlLog;
use App\Entity\CrawlSettings;
use App\Form\RobotLogType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class RobotLogController extends AbstractController
{
    #[Route('/robot/log', name: 'app_robot_log')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found.'
            );
        }

        $scanDates = null;
        $logs = null;

        $botId = $request->query->get('botId');
        $scanDate = $request->query->get('scanDate');
        if ($botId) {
            $scanDates = $doctrine->getRepository(CrawlLog::class)->findUniqueScanDatesByBotId($botId);
        }

        $crawler = $doctrine->getRepository(CrawlSettings::class)->findOneByBotId($botId);
        $crawlers = $doctrine->getRepository(CrawlSettings::class)->findAllByUserId($user->getId());
        $form = $this->createForm(RobotLogType::class, null, [
            'crawlers' => $crawlers,
            'crawler' => $crawler,
            'scan_date' => $scanDate,
            'scan_dates' => $scanDates,
        ]);

        if (($botId) && ($scanDate)) {
            $logs = $doctrine->getRepository(CrawlLog::class)->findAllByBotIdAndScanDate($botId, $scanDate);
        }
        return $this->renderForm('robot_log/index.html.twig', [
            'form' => $form,
            'logs' => $logs,
        ]);
    }
}
