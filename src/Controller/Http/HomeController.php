<?php

namespace App\Controller\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/oauth/callback', name: 'app_oauth_callback')]
    #[Route(
        '/{reactRoute}',
        name: 'app_react_route',
        requirements: ['reactRoute' => '^(?!api|_error|build|bundles|img|stitch).+'],
        priority: -10,
    )]
    public function __invoke(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
