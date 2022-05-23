<?php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\CrawlData;
use App\Repository\CrawlDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class RobotRecordsController extends AbstractController
{
    #[Route('/robot/records', name: 'app_robot_records')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found.'
            );
        }

        return $this->render('robot_records/index.html.twig');
    }

    #[Route('/robot/records/view/{botId}/record/{recordId}', name: 'app_records_show')]
    public function show(Request $request, ManagerRegistry $doctrine, int $botId, int $recordId): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found.'
            );
        }

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new \Exception(
                'User does not own bot.'
            );
        }

        $record = $doctrine->getRepository(CrawlData::class)->findOneById($recordId);
        $headers = $record->getMetadata();

        return $this->render('robot_records/record.html.twig', [
            'headers' => $headers,
            'data' => $record->getDataStream(),
        ]);
    }

    #[Route('/robot/records/{botId}/date/{scanDate}/offset/{offset}', name: 'app_records_view')]
    public function paginate(Request $request, CrawlDataRepository $recordsRepository, ManagerRegistry $doctrine, int $botId, string $scanDate, int $offset): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found.'
            );
        }

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
            throw new \Exception(
                'User does not own bot.'
            );
        }

        $crawler = $doctrine->getRepository(CrawlSettings::class)->findOneByUserIdAndBotId($user->getId(), $botId);
        if (!$crawler) {
            throw $this->createNotFoundException(
                'No bot for id: ' . $botId
            );
        }

        if ($offset < 0) {
            throw new \Exception(
                'Invalid offset.'
            );
        }

        if (!preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $scanDate, $matches)) {
            throw new \Exception(
                'Invalid scan date.'
            );
        }

        $paginator = $recordsRepository->getPaginator($botId, $scanDate, $offset);

        return $this->render('robot_records/view.html.twig', [
            'address' => $crawler->getAddress(),
            'previous' => $offset - CrawlDataRepository::PAGINATOR_PER_PAGE,
            'records' => $paginator,
            'next' => min(count($paginator), $offset + CrawlDataRepository::PAGINATOR_PER_PAGE),
            'bot_id' => $botId,
            'scan_date' => $scanDate,
        ]);
    }
}
