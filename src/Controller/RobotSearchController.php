<?php

namespace App\Controller;

use App\Entity\CrawlData;
use App\Repository\CrawlDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RobotSearchController extends AbstractController
{
    #[Route('/robot/search', name: 'app_robot_search', methods: ['GET', 'POST'])]
    public function index(Request $request, CrawlDataRepository $recordsRepository): Response
    {
        $offset = 0;
        $paginator = null;
        $searchTerm = "";

        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $searchTerm = $request->get('search') ?? null;
        $offset = $request->get('offset') ?? 0;

        $paginator = $recordsRepository->getSearchPaginator($searchTerm, $offset, $user->getId());
        $count = count($paginator);

        return $this->render('robot_search/index.html.twig', [
            'next' => min($count, $offset + CrawlDataRepository::PAGINATOR_PER_PAGE),
            'previous' => $offset - CrawlDataRepository::PAGINATOR_PER_PAGE,
            'records' => $paginator,
            'search_term' => $searchTerm,
            'count' => $count,
        ]);
    }
}
