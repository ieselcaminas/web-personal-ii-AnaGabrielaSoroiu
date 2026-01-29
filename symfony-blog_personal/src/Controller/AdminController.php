<?php

namespace App\Controller;

use App\Entity\Type;
use App\Entity\Menu;
use App\Form\TypeFormType;
use App\Form\MenuFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('/admin/menu', name: 'app_menu')]
    public function menus(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuFormType::class, $menu);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['file']->getData();

            if($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();
            }

            try {
                $uploadedFile->move(
                    $this->getParameter('menu_upload_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                return new Response("Error al subir el archivo: " . $e->getMessage());
            }

            $menu->setFile($newFilename);

            $menu = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($menu);
            $entityManager->flush();
        }

        $menus = $doctrine->getRepository(Menu::class)->findAll();


        return $this->render('admin/menu.html.twig', array(
            'form' => $form->createView(),
            'menus' => $menus
        ));
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
