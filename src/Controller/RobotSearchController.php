<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RobotSearchController extends AbstractController
{
    #[Route('/robot/search', name: 'app_robot_search')]
    public function index(): Response
    {
        return $this->render('robot_search/index.html.twig', [
        ]);
    }
}
