<?php

// src/Controller/ApiRobotRecordsController.php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\CrawlLaunch;
use App\Entity\CrawlData;
use App\Repository\CrawlDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;

class ApiRobotRecordsController extends AbstractController
{
    private $serializer = null;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    #[Route('/api/robot/records/{botId}/launch/{launchId}/offset/{offset}', name: 'app_api_robot_records_view')]
    public function paginate(Request $request, CrawlDataRepository $recordsRepository, ManagerRegistry $doctrine, int $botId, int $launchId, int $offset): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new AccessDeniedException(
                'User does not own bot.'
            );
        }

        $crawler = $doctrine->getRepository(CrawlSettings::class)->findOneByUserIdAndBotId($user->getId(), $botId);
        if (!$crawler) {
            throw $this->createNotFoundException(
                'No bot for id: ' . $botId
            );
        }

        if (($launchId < 0) || ($offset < 0)) {
            throw new \InvalidArgumentException(
                'Invalid offset.'
            );
        }

        $launch = $doctrine->getRepository(CrawlLaunch::class)->findOneByLaunchId($launchId);
        if (!$launch) {
            throw $this->createNotFoundException(
                'No launch for id: ' . $launchId
            );
        }

        $records = [];
        $paginator = $recordsRepository->getPaginator($launchId, $offset);

        $jsonContent = $this->serializer->serialize($paginator, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['data']]);

        $response = new JsonResponse();
        $response->setContent($jsonContent);

        return $response;
    }
}
