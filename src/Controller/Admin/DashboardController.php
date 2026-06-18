<?php

namespace App\Controller\Admin;

use App\Entity\Commentaire;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommentaireRepository;
use App\Repository\MembreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'app_admin_dashboard')]
    public function index(
        ArticleRepository $articleRepo,
        CategorieRepository $categorieRepo,
        MembreRepository $membreRepo,
        CommentaireRepository $commentaireRepo,
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'totalArticles' => $articleRepo->count([]),
            'totalCategories' => $categorieRepo->count([]),
            'totalMembres' => $membreRepo->count([]),
            'totalCommentaires' => $commentaireRepo->count([]),
            'totalCommentairesAttente' => $commentaireRepo->countByStatut(Commentaire::STATUT_EN_ATTENTE),
        ]);
    }
}
