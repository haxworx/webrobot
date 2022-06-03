<?php

namespace App\Controller;

use App\Repository\CrawlSettingsRepository;
use App\Repository\CrawlDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Persistence\ManagerRegistry;

class RobotRecordsQueryController extends AbstractController
{
    private $user = null;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    #[Route('/robot/records/dates/{botId}', name: 'app_robot_records_query_dates', methods: ['GET'], format: 'json')]
    public function dates(Request $request, CrawlSettingsRepository $settingsRepo, CrawlDataRepository $dataRepo, int $botId): Response
    {
        $jsonContent = [];

        $user = $this->getUser();
        if (!$user) {
            throw new \Exception(
                'No user.'
            );
        }

        if (!$settingsRepo->userOwnsBot($user->getId(), $botId)) {
            throw new \Exception(
                'User does not own bot.'
            );
        }

        $dates = $dataRepo->findUniqueScanDatesByBotId($botId);
        $jsonContent = $this->serializer->serialize($dates, 'json');

        $response = new Response();
        $response->setContent($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
    
        return $response;
    }
}
