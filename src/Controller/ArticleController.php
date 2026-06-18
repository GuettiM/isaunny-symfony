<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\CommentaireFormType;
use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    #[Route('/articles', name: 'app_articles')]
    public function index(ArticleRepository $articleRepo): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepo->findLatest(),
        ]);
    }

    #[Route('/article/{id}', name: 'app_article_show', requirements: ['id' => '\d+'])]
    public function show(
        Article $article,
        Request $request,
        EntityManagerInterface $em,
        CommentaireRepository $commentaireRepo,
    ): Response {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireFormType::class, $commentaire);

        // Seul un membre connecté peut poster (comme dans le code d'origine).
        if ($this->getUser()) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $commentaire->setArticle($article);
                $commentaire->setMembre($this->getUser());
                $commentaire->setDateCommentaire(new \DateTime());
                $commentaire->setStatut(Commentaire::STATUT_EN_ATTENTE);

                $em->persist($commentaire);
                $em->flush();

                $this->addFlash('success', 'Votre commentaire a bien été envoyé et est en attente de validation.');

                return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
            }
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'comments' => $commentaireRepo->findValidatedByArticle($article->getId()),
            'form' => $form->createView(),
        ]);
    }
}
