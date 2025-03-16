<?php

namespace App\Controller;

use App\Entity\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmailController extends AbstractController
{
    #[Route('/', name: 'email_list')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $limit = 10; // Количество писем на странице
        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * $limit;

        $repository = $em->getRepository(Email::class);

        $totalEmails = $repository->createQueryBuilder('e')
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $emails = $repository->createQueryBuilder('e')
            ->orderBy('e.receivedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $totalPages = ceil($totalEmails / $limit);

        return $this->render('email/index.html.twig', [
            'emails' => $emails,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }
}