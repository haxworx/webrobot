<?php

namespace App\Controller;

use App\Entity\GlobalSettings;
use App\Form\GlobalSettingsType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GlobalSettingsController extends AbstractController
{
    #[Route('/global/settings', name: 'app_global_settings')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $globalSettings = $doctrine->getRepository(GlobalSettings::class)->get();
        if (!$globalSettings) {
            throw $this->createNotFoundException(
                'No global settings found.'
            );
        }

        $form = $this->createForm(GlobalSettingsType::class, $globalSettings);
    
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($globalSettings);
            $entityManager->flush();
        }

        return $this->renderForm('global_settings/index.html.twig', [
            'form' => $form,
        ]);
    }
}
