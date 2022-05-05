<?php

namespace App\Controller;

use App\Entity\CrawlSettings;
use App\Entity\GlobalSettings;
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
        $settings = $doctrine->getRepository(GlobalSettings::class)->findOneBy(['id'=> '1']);
        $crawlSettings = new CrawlSettings();

        $form = $this->createForm(RobotScheduleType::class, $crawlSettings);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $repository = $doctrine->getRepository(CrawlSettings::class);
            $count = $repository->countByUserId($user->getId());

            if ($count < $settings->getMaxCrawlers()) {
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

                    $timer = new Timer($this->createTimerArgs($settings, $crawlSettings));
                    $timer->create();

                    $notifier->send(new Notification('Robot scheduled.', ['browser']));

                    $this->redirectToRoute('app_index');
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

    #[Route('/robot/schedule/edit/{bot_id}', name: 'app_robot_schedule_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier, int $bot_id): Response
    {
        $user = $this->getUser();
        $settings = $doctrine->getRepository(GlobalSettings::class)->findOneBy(['id'=> '1']);
        $crawlSettings = $doctrine->getRepository(CrawlSettings::class)->findOneBy(['botId' => $bot_id, 'userId' => $user->getId()]);
        if (!$crawlSettings) {
            throw $this->createNotFoundException(
                'No bot for id: ' . $bot_id
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
                return $this->redirectToRoute('app_robot_schedule_remove', ['bot_id' => $bot_id ]);
            }

            $parsed = parse_url($crawlSettings->getAddress());
            if ((array_key_exists('scheme', $parsed) && (array_key_exists('host', $parsed)))) {
                $crawlSettings->setScheme($parsed['scheme']);
                $crawlSettings->setDomain($parsed['host']);
            }

            $repository = $doctrine->getRepository(CrawlSettings::class);
            $isNewOrSame = $repository->isNewOrSame($user->getId(), $crawlSettings->getBotId(), $crawlSettings->getScheme(), $crawlSettings->getDomain());
            if ($isNewOrSame) {
                // Update our entity and save to database.
                $entityManager = $doctrine->getManager();
                $entityManager->persist($crawlSettings);
                $entityManager->flush();

                $timer = new Timer($this->createTimerArgs($settings, $crawlSettings));
                $timer->update();

                $notifier->send(new Notification('Robot scheduled.', ['browser']));
            } else {
                $notifier->send(new Notification('Robot already exists with that scheme and domain.', ['browser']));
            }
        }

        return $this->renderForm('robot_schedule/index.html.twig', [
            'form' => $form,
        ]);
    }

    private function createTimerArgs(GlobalSettings $settings, CrawlSettings $crawlSettings): array
    {
        $args = [
            'bot_id'       => $crawlSettings->getBotId(),
            'user_id'      => $crawlSettings->getUserId(),
            'domain'       => $crawlSettings->getDomain(),
            'address'      => $crawlSettings->getAddress(),
            'scheme'       => $crawlSettings->getScheme(),
            'agent'        => $crawlSettings->getAgent(),
            'time'         => $crawlSettings->getStartTime()->format('H:i:s'),
            'docker_image' => $settings->getDockerImage(),
        ];

        return $args;
    }

    #[Route('/robot/schedule/remove/{bot_id}', name: 'app_robot_schedule_remove')]
    public function remove(Request $request, ManagerRegistry $doctrine, NotifierInterface $notifier, int $bot_id): Response
    {
        $user = $this->getUser();
        $crawlSettings = $doctrine->getRepository(CrawlSettings::class)->findOneBy(['botId' => $bot_id, 'userId' => $user->getId()]);
        if (!$crawlSettings) {
            throw $this->createNotFoundException(
                'No bot for id: ' . $bot_id
            );
        }

        Timer::remove($crawlSettings->getBotId(), $crawlSettings->getUserId(), $crawlSettings->getScheme(), $crawlSettings->getDomain());

        $entityManager = $doctrine->getManager();

        $entityManager->remove($crawlSettings);
        $entityManager->flush();

        $notifier->send(new Notification('Robot removed.', ['browser']));

        return $this->redirectToRoute('app_index');
    }
}
