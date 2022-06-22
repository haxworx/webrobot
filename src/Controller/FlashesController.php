<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

class FlashesController extends AbstractController
{
    private $serializer = null;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    #[Route('/flashes', name: 'app_flashes', methods: ['POST'], format: 'json')]
    public function index(Request $request, NotifierInterface $notifier): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $content = json_decode($request->getContent(), true);

        if ((!$content) || (!array_key_exists('message', $content))) {
            throw new \Exception('Invalid request');
        } else {
            $message = $content['message'];

            $notifier->send(new Notification($message, ['browser']));

            $content = [
                'status' => 'OK',
            ];

            $jsonContent = $this->serializer->serialize($content, 'json');
            if (!$jsonContent) {
                throw new \Exception('Serialization failure');
            }

            $response = new Response();
            $response->setContent($jsonContent);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}
