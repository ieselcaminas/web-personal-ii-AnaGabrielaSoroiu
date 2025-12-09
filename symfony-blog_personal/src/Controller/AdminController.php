<?php

namespace App\Controller;

use App\Entity\Type;
use App\Form\TypeFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('/admin/menu', name: 'app_menu')]
    public function menus(): Response
    {
        return $this->render('admin/menu.html.twig', []);
    }

    #[Route('/admin/type', name: 'app_type')]
    public function type(ManagerRegistry $doctrine, Request $request): Response
    {
        $type = new Type();
        $form = $this->createForm(TypeFormType::class, $type);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $type = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($type);
            $entityManager->flush();
        } 
        
        $types = $doctrine->getRepository(Type::class)->findAll();

        return $this->render('admin/type.html.twig', array(
            'form' => $form->createView(),
            'types' => $types
        ));
    }
}
