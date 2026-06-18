<?php

namespace App\Controller\Admin;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/commentaires')]
#[IsGranted('ROLE_ADMIN')]
class CommentController extends AbstractController
{
    #[Route('', name: 'app_admin_comments')]
    public function index(CommentaireRepository $commentaireRepo): Response
    {
        return $this->render('admin/comment/index.html.twig', [
            'commentaires' => $commentaireRepo->findAllWithRelations(),
        ]);
    }

    #[Route('/{id}/valider', name: 'app_admin_comment_validate', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function validate(Commentaire $commentaire, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('validate_commentaire_'.$commentaire->getId(), $request->request->get('_token'))) {
            $commentaire->setStatut(Commentaire::STATUT_VALIDE);
            $em->flush();
        }

        return $this->redirectToRoute('app_admin_comments');
    }

    #[Route('/{id}/supprimer', name: 'app_admin_comment_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Commentaire $commentaire, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_commentaire_'.$commentaire->getId(), $request->request->get('_token'))) {
            $em->remove($commentaire);
            $em->flush();
        }

        return $this->redirectToRoute('app_admin_comments');
    }
}
