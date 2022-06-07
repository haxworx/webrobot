<?php

namespace App\Controller;

use App\Entity\CrawlLog;
use App\Entity\CrawlSettings;
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

        $content = json_decode($request->getContent(), true);

        if ((!$content) || (!array_key_exists('last_id', $content)) || (!array_key_exists('launch_id', $content)) ||
            (!array_key_exists('bot_id', $content)) || (!array_key_exists('token', $content))) {
            throw new \Exception('Missing parameters');
        }

        $lastId   = $content['last_id'];
        $launchId = $content['launch_id'];
        $botId    = $content['bot_id'];
        $token    = $content['token'];

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new \Exception('Bot not owned by user.');
        }

        if (!$this->isCsrfTokenValid('update-log', $token)) {
            throw new \Exception("CSRF Token Invalid");
        }

        $logs = $doctrine->getRepository(CrawlLog::class)->findAllNew($launchId, $lastId);
        $n = count($logs);
        if ($n) {
            $text = "";
            $content['last_id'] = $logs[$n - 1]->getId();
            foreach ($logs as $log) {
                $text .= $log->getId() . ':' .$log->getScanTimestamp()->format('Y-m-d H:i:s') . ':' . $log->getMessage() . "\n";
            }
            $content['logs'] = $text;
        }

        $response = new Response();
        $response->setContent(json_encode($content));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
