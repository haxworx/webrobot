<?php

// src/Controller/ApiRobotQueryController.php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\CrawlLog;
use App\Entity\CrawlLaunch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Persistence\ManagerRegistry;

class ApiRobotQueryController extends AbstractController
{
    private $serializer = null;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    #[Route('/api/robot/query/all', name: 'app_api_robot_query', format: 'json', methods: ['GET'])]
    public function all(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $crawlers = $doctrine->getRepository(CrawlSettings::class)->findAllByUserId($user->getId());
        $jsonContent = $this->serializer->serialize($crawlers, 'json');

        $response = new JsonResponse();
        $response->setContent($jsonContent);

        return $response;
    }

    #[Route('/api/robot/query/launches', name: 'app_api_robot_query_launches', methods: ['GET'], format: 'json')]
    public function launches(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (count($data) !== 1) {
            throw new \InvalidArgumentException("Length is not 1");
        }

        $botId = $data[0]['bot_id'];

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new AccessDeniedException(
                'Bot not owned by user.'
            );
        }

        $launches = $doctrine->getRepository(CrawlLaunch::class)->findOneByBotId($botId);

        $jsonContent = $this->serializer->serialize($launches, 'json');

        $response = new JsonResponse();
        $response->setContent($jsonContent);

        return $response;
    }
}
