<?php

namespace App\Controller\Admin;

use App\Repository\MembreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/membres')]
#[IsGranted('ROLE_ADMIN')]
class MemberController extends AbstractController
{
    #[Route('', name: 'app_admin_members')]
    public function index(MembreRepository $membreRepo): Response
    {
        return $this->render('admin/member/index.html.twig', [
            'membres' => $membreRepo->findAll(),
        ]);
    }
}
