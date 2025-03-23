<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EmailRepository;
use App\Entity\SearchModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmailController extends AbstractController
{
    #[Route('/', name: 'email_list')]
    public function index(Request $request, EmailRepository $emailRepository): Response
    {
        $page = $request->query->getInt('page', 1);

        $totalMessages = $emailRepository->createQueryBuilder('e')
            ->select('count(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $search = new SearchModel();

        $searchForm = $this->createFormBuilder($search)
            ->add('keyword', SearchType::class, ['attr' => [
                'class' => 'form-control me-2',
                'type' => 'search',
                'placeholder' => 'Найти',
                'aria-label' => 'Найти'
            ]])
            ->add('search', SubmitType::class, ['attr' => [
                'class' => 'btn btn-outline-success'
            ],
                'label' => 'Найти'])
            ->getForm();

        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchModel = $searchForm->getData();
            $messages = $emailRepository->findBySubject($searchModel->keyword);
            $totalPages = 1;
        } else {
            $messages = $emailRepository->getAllMessages($page);
            $totalPages = ceil($totalMessages / $emailRepository::MESSAGES_PER_PAGE);
        }

        return $this->render('email/index.html.twig', [
            'messages' => $messages,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[Route('/{sender_id}', name: 'show_email')]
    public function message(Request $request, EmailRepository $emailRepository): Response
    {
        $senderId = (int)$request->get('sender_id', 0);
        $messages = $emailRepository->getFromSender($senderId);

        return $this->render('email/message.html.twig', [
            'messages' => $messages
        ]);
    }
}