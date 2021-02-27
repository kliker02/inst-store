<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Payment;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\PaymentRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/client")
 */
class PaymentController extends AbstractController
{
    /**
     * @Route("/balance", name="app_client_payment_balance")
     */
    public function getlist(PaymentRepository $paymentRepository, PaginatorInterface $paginator, Request $request, EntityManagerInterface $em, \App\Repository\UserRepository $userRepository, \App\Service\UserServiceInterface $us): Response
    {
        $objUser = $userRepository->find($this->getUser()->getId());
        
        $us->updateBalance($objUser->getId());
        
        $rows = $paymentRepository->findBy(['User_ID' => $this->getUser()->getId(), 'Status' => [Payment::STATUS_PAID, Payment::STATUS_CANCELLED]]);

        $pagination = $paginator->paginate(
            $rows,
            $request->query->getInt('page', 1),
            $request->query->getInt('results', 10000)
        );

        return $this->render('payment/getlist.html.twig', [
            'pagination' => $pagination,
            'User' => $objUser,
        ]);
    }
}
