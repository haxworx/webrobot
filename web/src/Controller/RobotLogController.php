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

        return $this->render('robot_log/index.html.twig');
    }

    #[Route('/robot/log/more', name: 'app_robot_log_stream', format: 'json')]
    public function more(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found.'
            );
        }

        $obj = json_decode($request->getContent(), true);

        if ((!$obj) || (!array_key_exists('last_id', $obj)) || (!array_key_exists('scan_date', $obj)) ||
            (!array_key_exists('bot_id', $obj)) || (!array_key_exists('token', $obj))) {
            throw new \Exception('Missing parameters');
        }

        $lastId = $obj['last_id'];
        $scanDate = $obj['scan_date'];
        $botId = $obj['bot_id'];
        $token = $obj['token'];

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new \Exception('Bot not owned by user.');
        }

        if (!$this->isCsrfTokenValid('update-log', $token)) {
            throw new \Exception("CSRF Token Invalid");
        }

        $logs = $doctrine->getRepository(CrawlLog::class)->findAllNew($botId, $scanDate, $lastId);
        $n = count($logs);
        if ($n) {
            $text = "";
            $obj['last_id'] = $logs[$n - 1]->getId();
            foreach ($logs as $log) {
                $text .= $log->getId() . ':' .$log->getScanTimestamp()->format('Y-m-d H:i:s') . ':' . $log->getMessage() . "\n";
            }
            $obj['logs'] = $text;
        }

        $response = new Response();
        $response->setContent(json_encode($obj));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
