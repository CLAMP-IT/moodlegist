<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Packages;
use App\Form\MoodleVersionType;

class SearchController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/search', name: 'app_search')]
    public function index(Request $request): Response
    {
        $em = $this->entityManager;
        $repository = $em->getRepository(Packages::class);
        $qb = $repository->createQueryBuilder('p');
        $q = trim($request->get('q'));

        if (!empty($q)) {
            $qb
                ->where('p.name LIKE :name')
                ->addOrderBy('p.name', 'ASC')
                ->setParameter(':name', "%{$q}%");
        }

        $query = $qb->getQuery();

        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'searchForm' => $this->createForm(MoodleVersionType::class)->handleRequest($request)->createView(),
            'error' => '',
            'currentPageResults' => $query->getResult(),
            'moodle_version' => $request->get('moodle_version'),
            'title' => 'Moodle Packagist: Search packages',
        ]);
    }
}
