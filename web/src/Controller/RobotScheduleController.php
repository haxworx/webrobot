<?php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\GlobalSettings;
use App\Entity\CrawlData;
use App\Entity\CrawlErrors;
use App\Entity\CrawlLog;
use App\Utils\Timer;
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

        $crawlSettings = new CrawlSettings();

        $form = $this->createForm(RobotScheduleType::class, $crawlSettings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = $doctrine->getRepository(CrawlSettings::class);
            $count = $repository->countByUserId($user->getId());

            if ($count >= $globalSettings->getMaxCrawlers()) {
                $notifier->send(new Notification('Reached maximum number of crawlers ('. $count .').', ['browser']));
            } else {
                $crawlSettings->setSchemeAndDomain();

                $exists = $repository->settingsExists($crawlSettings, $user->getId());
                if ($exists) {
                    $notifier->send(new Notification('Robot already exists with that scheme and domain.', ['browser']));
                } else {
                    $crawlSettings->setUserId($user->getId());
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($crawlSettings);
                    $entityManager->flush();

                    // Create our systemd timer.
                    $timer = new Timer($globalSettings, $crawlSettings);
                    if ($timer->create()) {
                        $notifier->send(new Notification('Robot scheduled.', ['browser']));
                    } else {
                        $entityManager->remove($crawlSettings);
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
            'delete_path' => false,
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

        $crawlSettings = $doctrine->getRepository(CrawlSettings::class)->findOneByBotId($botId);
        if (!$crawlSettings) {
            throw $this->createNotFoundException(
                'No bot for id: ' . $botId
            );
        }

        $form = $this->createForm(RobotScheduleType::class, $crawlSettings, array(
            'save_button_label' => 'Update',
            'delete_button_hidden' => false,
            'ignore_query' => $crawlSettings->getIgnoreQuery(),
            'import_sitemaps' => $crawlSettings->getImportSitemaps(),
            'address_readonly' => true,
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = $doctrine->getRepository(CrawlSettings::class);
            $isSame = $repository->isSameAddress($crawlSettings, $user->getId());
            if (!$isSame) {
                $notifier->send(new Notification('Unable to change existing domain.', ['browser']));
            } else {
                // Update our entity and save to database.
                $entityManager = $doctrine->getManager();
                $entityManager->persist($crawlSettings);

                // Update our systemd timer.
                $timer = new Timer($globalSettings, $crawlSettings);
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
            'delete_path' => $this->generateUrl('app_robot_schedule_remove', ['botId' => $botId ]),
        ]);
    }

    #[Route('/robot/schedule/remove/{botId}', name: 'app_robot_schedule_remove')]
    public function remove(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier, int $botId): Response
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

        $crawlSettings = $doctrine->getRepository(CrawlSettings::class)->findOneByBotId($botId);
        if (!$crawlSettings) {
            throw $this->createNotFoundException(
                'No bot for id: ' . $botId
            );
        }

        // Remove our systemd timer.
        $timer = new Timer($globalSettings, $crawlSettings);
        $timer->remove();

        // Remove our database data related to the bot id.
        $entityManager = $doctrine->getManager();

        $crawlErrors = $doctrine->getRepository(CrawlErrors::class)->findAllByBotId($botId);
        foreach ($crawlErrors as $crawlError) {
            $entityManager->remove($crawlError);
        }

        $crawlData = $doctrine->getRepository(CrawlData::class)->findAllByBotId($botId);
        foreach ($crawlData as $data) {
            $entityManager->remove($data);
        }

        $crawlLog = $doctrine->getRepository(CrawlLog::class)->findAllByBotId($botId);
        foreach ($crawlLog as $log) {
            $entityManager->remove($log);
        }

        $entityManager->remove($crawlSettings);
        $entityManager->flush();

        $notifier->send(new Notification('Robot removed.', ['browser']));

        return $this->redirectToRoute('app_index');
    }
}
