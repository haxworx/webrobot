<?php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\CrawlData;
use App\Entity\CrawlErrors;
use App\Entity\CrawlLog;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class DeleteAccountController extends AbstractController
{
    #[Route('/delete/account', name: 'app_delete_account', methods: ['POST'])]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $token = $request->request->get('token');

        if (!$this->isCsrfTokenValid('delete-account', $token)) {
            throw new \Exception('Invalid CSRF token');
        }

        $crawlers = $doctrine->getRepository(CrawlSettings::class)->findAllByUserId($user->getId());
        foreach ($crawlers as $crawler) {
            $botId = $crawler->getBotId();
            $doctrine->getRepository(CrawlData::class)->deleteAllByBotId($botId);
            $doctrine->getRepository(CrawlErrors::class)->deleteAllByBotId($botId);
            $doctrine->getRepository(CrawlLog::class)->deleteAllByBotId($botId);
        }
        $doctrine->getRepository(CrawlSettings::class)->deleteAllByUserId($user->getId());

        $entityManager = $doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        $request->getSession()->invalidate();
        $this->container->get('security.token_storage')->setToken(null);

        return $this->redirectToRoute("app_index");
    }
}
