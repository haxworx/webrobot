<?php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\GlobalSettings;
use App\Entity\CrawlData;
use App\Entity\CrawlErrors;
use App\Entity\CrawlLog;
use App\Form\RobotScheduleType;
use App\Service\Mqtt;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;

class RobotScheduleController extends AbstractController
{
    #[Route('/robot/schedule', name: 'app_robot_schedule')]
    public function index(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException(
                'No user found.'
            );
        }

        $globalSettings = $doctrine->getRepository(GlobalSettings::class)->get();
        if (!$globalSettings) {
            throw $this->createNotFoundException(
                'No global settings found.'
            );
        }

        $crawler = new CrawlSettings();

        $form = $this->createForm(RobotScheduleType::class, $crawler);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = $doctrine->getRepository(CrawlSettings::class);
            $count = $repository->countByUserId($user->getId());

            if ($count >= $globalSettings->getMaxCrawlers()) {
                $notifier->send(new Notification('Reached maximum number of crawlers ('. $count .').', ['browser']));
            } else {
                $crawler->setSchemeAndDomain();

                $exists = $repository->settingsExists($crawler, $user->getId());
                if ($exists) {
                    $notifier->send(new Notification('Robot already exists with that scheme and domain.', ['browser']));
                } else {
                    $crawler->setUserId($user->getId());
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($crawler);
                    $entityManager->flush();

                    $notifier->send(new Notification('Robot scheduled.', ['browser']));

                    return $this->redirectToRoute('app_index');
                }
            }
        }

        return $this->renderForm('robot_schedule/index.html.twig', [
            'form' => $form,
            'is_edit' => false,
        ]);
    }

    #[Route('/robot/schedule/edit/{botId}', name: 'app_robot_schedule_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier, int $botId): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException(
                'No user found.'
            );
        }

        $globalSettings = $doctrine->getRepository(GlobalSettings::class)->get();
        if (!$globalSettings) {
            throw $this->createNotFoundException(
                'No global settings found.'
            );
        }

        $crawler = $doctrine->getRepository(CrawlSettings::class)->findOneByUserIdAndBotId($user->getId(), $botId);
        if (!$crawler) {
            throw $this->createNotFoundException(
                'No bot for id: ' . $botId
            );
        }

        $form = $this->createForm(RobotScheduleType::class, $crawler, array(
            'save_button_label' => 'Update',
            'delete_button_hidden' => false,
            'ignore_query' => $crawler->getIgnoreQuery(),
            'import_sitemaps' => $crawler->getImportSitemaps(),
            'address_readonly' => true,
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = $doctrine->getRepository(CrawlSettings::class);
            $isSame = $repository->isSameAddress($crawler, $user->getId());
            if (!$isSame) {
                $notifier->send(new Notification('Unable to change existing domain.', ['browser']));
            } else {
                // Update our entity and save to database.
                $entityManager = $doctrine->getManager();
                $entityManager->persist($crawler);

                // Stop any running container.
                $mqtt = new Mqtt($globalSettings);
                $mqtt->stopRobot($botId);

                $entityManager->flush();
                $notifier->send(new Notification('Robot scheduled.', ['browser']));
            }
        }

        return $this->renderForm('robot_schedule/index.html.twig', [
            'form' => $form,
            'bot_id' => $botId,
            'is_edit' => true,
        ]);
    }

    // See public/robot_schedule.js for POST request.
    #[Route('/robot/schedule/remove/{botId}', name: 'app_robot_schedule_remove', methods: ['POST'])]
    public function remove(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException(
                'No user found.'
            );
        }

        $globalSettings = $doctrine->getRepository(GlobalSettings::class)->get();
        if (!$globalSettings) {
            throw $this->createNotFoundException(
                'No global settings found.'
            );
        }

        $botId = $request->request->get('botId');
        $token = $request->request->get('token');

        if (!$this->isCsrfTokenValid('remove-crawler', $token)) {
            throw new \Exception('Invalid CSRF token');
        }

        $crawler = $doctrine->getRepository(CrawlSettings::class)->findOneByUserIdAndBotId($user->getId(), $botId);
        if (!$crawler) {
            throw $this->createNotFoundException(
                'No bot for id: ' . $botId
            );
        }

        // Stop any running container.
        $mqtt = new Mqtt($globalSettings);
        $mqtt->stopRobot($botId);

        // Remove our database data related to the bot id.
        $doctrine->getRepository(CrawlData::class)->deleteAllByBotId($botId);
        $doctrine->getRepository(CrawlErrors::class)->deleteAllByBotId($botId);
        $doctrine->getRepository(CrawlLog::class)->deleteAllByBotId($botId);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($crawler);
        $entityManager->flush();

        $notifier->send(new Notification('Robot removed.', ['browser']));

        return $this->redirectToRoute('app_index');
    }
}
