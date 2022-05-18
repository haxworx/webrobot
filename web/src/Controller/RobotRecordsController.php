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
        $scanDates = null;
        $botId = $request->query->getInt('botId', 0);
        if (($botId < 0)) {
            throw new \Exception(
                'Invalid bot id.'
            );
        }
        if (($botId) && ($doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId))) {
            $scanDates = $doctrine->getRepository(CrawlData::class)->findUniqueScanDatesByBotId($botId);
        }

        $crawlers = $doctrine->getRepository(CrawlSettings::class)->findAllByUserId($user->getId());

        return $this->render('robot_records/index.html.twig', [
            'crawlers' => $crawlers,
            'scan_dates' => $scanDates,
            'bot_id' => $botId,
        ]);
    }

    #[Route('/robot/records/{botId}/date/{scanDate}', name: 'app_records_view')]
    public function paginate(Request $request, CrawlDataRepository $recordsRepository, ManagerRegistry $doctrine, int $botId, string $scanDate): Response
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

        $offset = $request->query->getInt('offset', 0);
        if ((!is_int($offset)) || ($offset < 0)) {
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
