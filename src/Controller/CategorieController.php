<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategorieController extends AbstractController
{
    #[Route('/categories', name: 'app_categories')]
    public function index(CategorieRepository $categorieRepo): Response
    {
        return $this->render('categorie/index.html.twig', [
            'categories' => $categorieRepo->findAllOrderedByNom(),
        ]);
    }

    #[Route('/categorie/{id}', name: 'app_categorie_show', requirements: ['id' => '\d+'])]
    public function show(Categorie $categorie, ArticleRepository $articleRepo): Response
    {
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
            'articles' => $articleRepo->findByCategorie($categorie),
        ]);
    }
}
