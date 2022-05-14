<?php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\GlobalSettings;
use App\Entity\CrawlData;
use App\Entity\CrawlErrors;
use App\Entity\CrawlLog;
use App\Service\Timer;
use App\Service\Docker;
use App\Form\RobotScheduleType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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
            throw $this->createNotFoundException(
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

                    // Create our systemd timer.
                    $timer = new Timer($globalSettings, $crawler);
                    if ($timer->create()) {
                        $notifier->send(new Notification('Robot scheduled.', ['browser']));
                    } else {
                        $entityManager->remove($crawler);
                        $entityManager->flush();
                        $notifier->send(new Notification('There was a problem scheduling the robot.', ['browser']));
                        return $this->redirectToRoute('app_robot_schedule');
                    }

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
            throw $this->createNotFoundException(
                'No user found.'
            );
        }

        $globalSettings = $doctrine->getRepository(GlobalSettings::class)->get();
        if (!$globalSettings) {
            throw $this->createNotFoundException(
                'No global settings found.'
            );
        }

        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
           throw new \Exception('Bot not owned by user.');
        }

        $crawler = $doctrine->getRepository(CrawlSettings::class)->findOneByBotId($botId);
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
                $containerId = $crawler->getContainerId();
                if ($containerId) {
                    $container = new Docker($containerId);
                    $container->stop();
                }

                // Update our systemd timer.
                $timer = new Timer($globalSettings, $crawler);
                if (!$timer->update()) {
                    $notifier->send(New Notification('There was a problem updating the schedule.', ['browser']));
                } else {
                    $entityManager->flush();
                    $notifier->send(new Notification('Robot scheduled.', ['browser']));
                }
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
            throw $this->createNotFoundException(
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
        if (!$doctrine->getRepository(CrawlSettings::class)->userOwnsBot($user->getId(), $botId)) {
           throw new \Exception('Bot not owned by user.');
        }

        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid('remove-crawler', $token)) {
            throw new \Exception('Invalid CSRF token');
        }

        $crawler = $doctrine->getRepository(CrawlSettings::class)->findOneByBotId($botId);
        if (!$crawler) {
            throw $this->createNotFoundException(
                'No bot for id: ' . $botId
            );
        }

        // Stop any running container.
        $containerId = $crawler->getContainerId();
        if ($containerId) {
            $container = new Docker($containerId);
            $container->stop();
        }

        // Remove our systemd timer.
        $timer = new Timer($globalSettings, $crawler);
        $timer->remove();

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
