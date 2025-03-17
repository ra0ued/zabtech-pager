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
        $messageLimit = 10;
        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * $messageLimit;

        $repository = $em->getRepository(Email::class);

        $totalMessages = $repository->createQueryBuilder('e')
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $messages = $repository->createQueryBuilder('e')
            ->orderBy('e.receivedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($messageLimit)
            ->getQuery()
            ->getResult();

        $totalPages = ceil($totalMessages / $messageLimit);

        return $this->render('email/index.html.twig', [
            'messages' => $messages,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }
}