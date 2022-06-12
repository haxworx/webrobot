<?php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\CrawlData;
use App\Entity\CrawlLaunch;
use App\Repository\CrawlLaunchRepository;
use App\Repository\CrawlDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Persistence\ManagerRegistry;

class RobotRecordsController extends AbstractController
{
    #[Route('/robot/records', name: 'app_robot_records')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        return $this->render('robot_records/index.html.twig');
    }

    #[Route('/robot/records/download/{botId}/record/{recordId}', name: 'app_records_download')]
    public function download(Request $request, ManagerRegistry $doctrine, int $botId, int $recordId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new AccessDeniedException(
                'User does not own bot.'
            );
        }

        $record = $doctrine->getRepository(CrawlData::class)->findOneById($recordId);
        if (!$record) {
            throw $this->createNotFoundException(
                'No record found.'
            );
        }

        $fileName = $record->getId();
        $blob = $record->getDataStream();

        $response = new Response();
        $response->headers->set('Content-Type', $record->getContentType());
        $response->headers->set('Content-Length', strlen($blob));
        $response->headers->set('Content-Disposition', 'attachment; filename="'. $fileName .'"');
        $response->setContent($blob);

        return $response;
    }

    #[Route('/robot/records/show/{botId}/record/{recordId}', name: 'app_records_show')]
    public function show(Request $request, ManagerRegistry $doctrine, int $botId, int $recordId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new AccessDeniedException(
                'User does not own bot.'
            );
        }

        $record = $doctrine->getRepository(CrawlData::class)->findOneById($recordId);
        if (!$record) {
            throw $this->createNotFoundException(
                'No record found.'
            );
        }

        $headers = $record->getMetadata();

        return $this->render('robot_records/record.html.twig', [
            'headers' => $headers,
            'data' => $record->getDataStream(),
        ]);
    }

    #[Route('/robot/records/{botId}/launch/{launchId}/offset/{offset}', name: 'app_records_view')]
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
            throw new \Exception(
                'Invalid offset.'
            );
        }

        $launch = $doctrine->getRepository(CrawlLaunch::class)->findOneByLaunchId($launchId);
        if (!$launch) {
            throw $this->createNotFoundException(
                'No launch for id: ' . $launchId
            );
        }

        $timeRange = $launch->getStartTime()->format('Y-m-d H:i:s') . ' to ' . $launch->getEndTime()->format('Y-m-d H:i:s');

        $paginator = $recordsRepository->getPaginator($launchId, $offset);

        return $this->render('robot_records/view.html.twig', [
            'address' => $crawler->getAddress(),
            'previous' => $offset - CrawlDataRepository::PAGINATOR_PER_PAGE,
            'records' => $paginator,
            'next' => min(count($paginator), $offset + CrawlDataRepository::PAGINATOR_PER_PAGE),
            'bot_id' => $botId,
            'launch_id' => $launchId,
            'time_range' => $timeRange,
        ]);
    }
}
