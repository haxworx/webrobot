<?php

namespace App\Controller;

use App\Utils\ApiKey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Persistence\ManagerRegistry;

class ApiKeyController extends AbstractController
{
    #[Route('/api/key/regenerate', name: 'app_api_key', format: 'json', methods: ['POST'])]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $content = json_decode($request->getContent(), true);
        if ((!$content) || (!array_key_exists('token', $content))) {
            throw new \Exception('Missing parameters');
        }

        $token = $content['token'];

        if (!$this->isCsrfTokenValid('regenerate-api-key', $token)) {
            throw new AccessDeniedException('CSRF token invalid');
        }

        $newKey = ApiKey::generate();
        $user->setApiToken($newKey);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $response = new JsonResponse(['api-key' => $newKey, 'message' => 'ok']);

        return $response;
    }
}
