<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\MoodleVersionType;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(MoodleVersionType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('app_search', $data);
        }

        return $this->render('index/index.html.twig', [
            'title' => 'Moodle Packagist: Manage your plugins with Composer',
            'controller_name' => 'IndexController',
            'searchForm' => $form,
        ]);
    }
}
