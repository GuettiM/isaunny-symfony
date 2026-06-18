<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepo, CategorieRepository $categorieRepo): Response
    {
        $featured = $articleRepo->findLatest(1);
        $featuredArticle = $featured[0] ?? null;

        return $this->render('home/index.html.twig', [
            'categories' => $categorieRepo->findAllOrderedByNom(),
            'articles' => $articleRepo->findLatest(6, 1),
            'featuredArticle' => $featuredArticle,
        ]);
    }
}
