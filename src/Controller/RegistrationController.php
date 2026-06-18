<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Form\RegistrationFormType;
use App\Repository\MembreRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        MembreRepository $membreRepo,
        MailService $mailService,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $membre = new Membre();
        $form = $this->createForm(RegistrationFormType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Email déjà utilisé ?
            if ($membreRepo->findOneByEmail($membre->getEmail())) {
                $this->addFlash('danger', 'Cet email existe déjà.');
                return $this->redirectToRoute('app_register');
            }

            $membre->setRole('membre');
            $membre->setPassword(
                $passwordHasher->hashPassword($membre, $form->get('plainPassword')->getData())
            );

            $em->persist($membre);
            $em->flush();

            if ($mailService->sendWelcomeEmail($membre->getEmail(), $membre->getPseudo())) {
                $this->addFlash('success', 'Inscription réussie. Email de bienvenue envoyé.');
            } else {
                $this->addFlash('success', 'Inscription réussie, mais l\'email n\'a pas pu être envoyé.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
