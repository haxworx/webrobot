<?php

// src/Controller/RobotQueryController.php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\CrawlLog;
use App\Entity\CrawlLaunch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Persistence\ManagerRegistry;

class RobotQueryController extends AbstractController
{
    private $serializer = null;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    #[Route('/robot/query/all', name: 'app_robot_query_all', methods: ['GET'], format: 'json')]
    public function all(Request $request, ManagerRegistry $doctrine): Response
    {
        $jsonContent = [];
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $crawlers = $doctrine->getRepository(CrawlSettings::class)->findAllByUserId($user->getId());
        $jsonContent = $this->serializer->serialize($crawlers, 'json');

        $response = new Response();
        $response->setContent($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    #[Route('/robot/query/launches/{botId}', name: 'app_robot_query_launches', methods: ['GET'], format: 'json')]
    public function launches(Request $request, ManagerRegistry $doctrine, int $botId): Response
    {
        $jsonContent = [];
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new AccessDeniedException(
                'Bot not owned by user.'
            );
        }

        $launches = $doctrine->getRepository(CrawlLaunch::class)->findByBotId($botId);

        $jsonContent = $this->serializer->serialize($launches, 'json');

        $response = new Response();
        $response->setContent($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
