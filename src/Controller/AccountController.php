<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Form\AccountFormType;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/compte', name: 'app_account')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        MembreRepository $membreRepo,
    ): Response {
        /** @var Membre $membre */
        $membre = $this->getUser();

        $form = $this->createForm(AccountFormType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Email pris par un autre compte ?
            $existing = $membreRepo->findOneByEmail($membre->getEmail());
            if ($existing && $existing->getId() !== $membre->getId()) {
                $this->addFlash('danger', 'Cet email est déjà utilisé par un autre compte.');
                return $this->redirectToRoute('app_account');
            }

            $em->flush();
            $this->addFlash('success', 'Vos informations ont bien été mises à jour.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/index.html.twig', [
            'form' => $form->createView(),
            'membre' => $membre,
        ]);
    }
}
