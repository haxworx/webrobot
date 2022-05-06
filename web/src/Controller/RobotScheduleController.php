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

            if ($count < $globalSettings->getMaxCrawlers()) {
                $parsed = parse_url($crawlSettings->getAddress());
                if ((array_key_exists('scheme', $parsed) && (array_key_exists('host', $parsed)))) {
                    $crawlSettings->setScheme($parsed['scheme']);
                    $crawlSettings->setDomain($parsed['host']);
                }

                $exists = $doctrine->getRepository(CrawlSettings::class)->findOneBy(
                    ['userId' => $user->getId(), 'scheme' => $crawlSettings->getScheme(), 'domain' => $crawlSettings->getDomain()]
                );

                if (!$exists) {
                    $crawlSettings->setUserId($user->getId());
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($crawlSettings);
                    $entityManager->flush();

                    // Create our systemd timer.
                    $timer = new Timer($globalSettings, $crawlSettings);
                    $timer->create();

                    $notifier->send(new Notification('Robot scheduled.', ['browser']));

                    return $this->redirectToRoute('app_index');
                } else {
                    $notifier->send(new Notification('Robot already exists with that scheme and domain.', ['browser']));
                }
            } else {
                $notifier->send(new Notification('Reached maximum number of crawlers ('. $count .').', ['browser']));
            }
        }

        return $this->renderForm('robot_schedule/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/robot/schedule/edit/{botId}', name: 'app_robot_schedule_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier, int $botId): Response
    {
        $user = $this->getUser();
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
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Was 'Delete' button clicked?
            if ($form->get('delete')->isClicked()) {
                return $this->redirectToRoute('app_robot_schedule_remove', ['botId' => $botId ]);
            }

            $parsed = parse_url($crawlSettings->getAddress());
            if ((array_key_exists('scheme', $parsed) && (array_key_exists('host', $parsed)))) {
                $crawlSettings->setScheme($parsed['scheme']);
                $crawlSettings->setDomain($parsed['host']);
            }

            $repository = $doctrine->getRepository(CrawlSettings::class);
            $isNewOrSame = $repository->isNewOrSame($user->getId(), $crawlSettings->getBotId(), $crawlSettings->getScheme(), $crawlSettings->getDomain());
            if (!$isNewOrSame) {
                $notifier->send(new Notification('Robot already exists with that scheme and domain.', ['browser']));
            } else {
                // Update our entity and save to database.
                $entityManager = $doctrine->getManager();
                $entityManager->persist($crawlSettings);
                $entityManager->flush();

                // Update our systemd timer.
                $timer = new Timer($globalSettings, $crawlSettings);
                $timer->update();

                $notifier->send(new Notification('Robot scheduled.', ['browser']));
            }
        }

        return $this->renderForm('robot_schedule/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/robot/schedule/remove/{botId}', name: 'app_robot_schedule_remove')]
    public function remove(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier, int $botId): Response
    {
        $user = $this->getUser();

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
