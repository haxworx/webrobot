<?php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\GlobalSettings;
use App\Entity\CrawlData;
use App\Entity\CrawlErrors;
use App\Entity\CrawlLog;
use App\Service\Mqtt;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class ApiRobotScheduleController extends AbstractController
{
    #[Route('/api/robot/schedule', format: 'json', name: 'app_api_robot_schedule', methods: ['POST'])]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $globalSettings = $doctrine->getRepository(GlobalSettings::class)->get();
        if (!$globalSettings) {
            throw $this->createNotFoundException(
                'No global settings found.'
            );
        }

        $data = json_decode($request->getContent(), true);
        $len = count($data);
        if ($len !== 1) {
            throw new \Exception("Invalid argument.");
        }

        $data = $data[0];

        $agent = isset($data['agent']) ? $data['agent'] : null;
        $address = isset($data['address']) ? $data['address'] : null;
        $delay = isset($data['delay']) ? $data['delay'] : null;
        $ignoreQuery = isset($data['ignore_query']) ? $data['ignore_query'] : null;
        $importSitemaps = isset($data['import_sitemaps']) ? $data['import_sitemaps'] : null;
        $retryMax = isset($data['retry_max']) ? $data['retry_max'] : null;
        $startTime = isset($data['start_time']) && preg_match('/^\d{1,2}:\d{1,2}$/', $data['start_time']) ? new \DateTime($data['start_time']) : null;

        if (($agent === null) || ($address === null) || ($delay === null) || ($ignoreQuery === null) || ($importSitemaps === null) || ($retryMax === null) || ($startTime === null)) {
            throw new \Exception("Invalid argument.");
        }

        $repository = $doctrine->getRepository(CrawlSettings::class);
        $count = $repository->countByUserId($user->getId());
        if ($count >= $globalSettings->getMaxCrawlers()) {
            throw new \Exception('Maximum number of crawlers reached');
        }

        $crawler = new CrawlSettings();
        $crawler->setAddress($address);
        $crawler->setSchemeAndDomain();

        $exists = $repository->settingsExists($crawler, $user->getId());
        if ($exists) {
            throw new \Exception('Robot already exists with that scheme and domain.');
        }

        $crawler->setAgent($agent);
        $crawler->setDelay($delay);
        $crawler->setIgnoreQuery($ignoreQuery);
        $crawler->setImportSitemaps($importSitemaps);
        $crawler->setRetryMax($retryMax);
        $crawler->setStartTime($startTime);
        $crawler->setUserId($user->getId());

        $entityManager = $doctrine->getManager();
        $entityManager->persist($crawler);
        $entityManager->flush();

        $obj = [
            'message' => 'ok',
            'bot_id' => $crawler->getBotId(),
        ];

        $response = new JsonResponse($obj);

        return $response;
    }

    #[Route('/api/robot/schedule/edit', format: 'json', name: 'app_api_robot_schedule_edit', methods: ['PATCH'])]
    public function edit(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $globalSettings = $doctrine->getRepository(GlobalSettings::class)->get();
        if (!$globalSettings) {
            throw $this->createNotFoundException(
                'No global settings found.'
            );
        }

        $data = json_decode($request->getContent(), true);
        $len = count($data);
        if ($len !== 1) {
            throw new \Exception("Invalid argument.");
        }

        $data = $data[0];

        $botId = isset($data['bot_id']) ? $data['bot_id'] : null;
        $agent = isset($data['agent']) ? $data['agent'] : null;
        $delay = isset($data['delay']) ? $data['delay'] : null;
        $ignoreQuery = isset($data['ignore_query']) ? $data['ignore_query'] : null;
        $importSitemaps = isset($data['import_sitemaps']) ? $data['import_sitemaps'] : null;
        $retryMax = isset($data['retry_max']) ? $data['retry_max'] : null;
        $startTime = isset($data['start_time']) && preg_match('/^\d{1,2}:\d{1,2}$/', $data['start_time']) ? new \DateTime($data['start_time']) : null;

        if (($botId === null) || ($agent === null) || ($delay === null) || ($ignoreQuery === null) || ($importSitemaps === null) || ($retryMax === null) || ($startTime === null)) {
            throw new \Exception("Invalid argument.");
        }

        $crawler = $doctrine->getRepository(CrawlSettings::class)->findOneByUserIdAndBotId($user->getId(), $botId);
        if (!$crawler) {
            throw $this->createNotFoundException(
                'Not bot for id: ' . $botId
            );
        }

        $crawler->setAgent($agent);
        $crawler->setDelay($delay);
        $crawler->setIgnoreQuery($ignoreQuery);
        $crawler->setImportSitemaps($importSitemaps);
        $crawler->setRetryMax($retryMax);
        $crawler->setStartTime($startTime);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($crawler);

        // Stop any running container.
        $mqtt = new Mqtt($globalSettings);
        $mqtt->stopRobot($botId);

        $entityManager->flush();

        $response = new JsonResponse(['message' => 'ok']);

        return $response;
    }

    #[Route('/api/robot/schedule/remove', format: 'json', name: 'app_api_robot_schedule_remove', methods: ['DELETE'])]
    public function remove(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $globalSettings = $doctrine->getRepository(GlobalSettings::class)->get();
        if (!$globalSettings) {
            throw $this->createNotFoundException(
                'No global settings found.'
            );
        }
        $data = json_decode($request->getContent(), true);
        $len = count($data);
        if ($len !== 1) {
            throw new \Exception("Invalid argument.");
        }

        $data = $data[0];

        $botId = isset($data['bot_id']) ? $data['bot_id'] : null;
        if ($botId === null) {
            throw new \Exception('Invalid argument.');
        }

        $crawler = $doctrine->getRepository(CrawlSettings::class)->findOneByUserIdAndBotId($user->getId(), $botId);
        if (!$crawler) {
            throw $this->createNotFoundException(
                'No bot for id: ' . $botId
            );
        }

        $mqtt = new Mqtt($globalSettings);
        $mqtt->stopRobot($botId);

        // Remove our database data related to the bot id.
        $doctrine->getRepository(CrawlData::class)->deleteAllByBotId($botId);
        $doctrine->getRepository(CrawlErrors::class)->deleteAllByBotId($botId);
        $doctrine->getRepository(CrawlLog::class)->deleteAllByBotId($botId);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($crawler);
        $entityManager->flush();
        $response = new JsonResponse(['message' => 'ok']);

        return $response;
    }
}
