<?php


// src/Controller/RobotLogController.php

namespace App\Controller;

use App\Entity\CrawlLog;
use App\Entity\CrawlSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Persistence\ManagerRegistry;

class RobotLogController extends AbstractController
{
    #[Route('/robot/log', name: 'app_robot_log')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        return $this->render('robot_log/index.html.twig');
    }

    #[Route('/robot/log/more', name: 'app_robot_log_stream', format: 'json')]
    public function more(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $content = json_decode($request->getContent(), false);
        if ((!$content) || (!isset($content->lastId)) || (!isset($content->launchId)) || (!isset($content->botId)) || (!isset($content->token))) {
            throw new \InvalidArgumentException('Missing parameters');
        }

        $lastId   = $content->lastId;
        $launchId = $content->launchId;
        $botId    = $content->botId;
        $token    = $content->token;

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new AccessDeniedException('Bot not owned by user.');
        }

        if (!$this->isCsrfTokenValid('update-log', $token)) {
            throw new AccessDeniedException("CSRF Token Invalid");
        }

        $logs = $doctrine->getRepository(CrawlLog::class)->findAllNew($launchId, $lastId);
        $n = count($logs);
        if ($n) {
            $text = "";
            $content->lastId = $logs[$n - 1]->getId();
            foreach ($logs as $log) {
                $text .= $log->getScanTimestamp()->format('Y-m-d H:i:s') . ':' . $log->getMessage() . "\n";
            }
            $content->logs = $text;
        }

        $response = new JsonResponse($content);

        return $response;
    }
}
