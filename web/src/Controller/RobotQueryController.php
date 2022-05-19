<?php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\CrawlLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Persistence\ManagerRegistry;

class RobotQueryController extends AbstractController
{
    private $serializer = null;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];;
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    #[Route('/robot/query/all', name: 'app_robot_query_all', methods: ['GET'], format: 'json')]
    public function all(Request $request, ManagerRegistry $doctrine): Response
    {
        $jsonContent = [];

        $user = $this->getUser();
        if (!$user) {
            throw new \Exception(
                'No user.'
            );
        }

        $crawlers = $doctrine->getRepository(CrawlSettings::class)->findAllByUserId($user->getId());
        $jsonContent = $this->serializer->serialize($crawlers, 'json');

        $response = new Response();
        $response->setContent($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    #[Route('/robot/query/dates/{botId}', name: 'app_robot_query_dates', methods: ['GET'], format: 'json')]
    public function dates(Request $request, ManagerRegistry $doctrine, int $botId): Response
    {
        $jsonContent = [];

        $user = $this->getUser();
        if (!$user) {
            throw new \Exception(
                'No user.'
            );
        }

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new \Exception(
                'Bot not owned by user.'
            );
        }

        $dates = $doctrine->getRepository(CrawlLog::class)->findUniqueScanDatesByBotId($botId);
        $jsonContent = $this->serializer->serialize($dates, 'json');

        $response = new Response();
        $response->setContent($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
