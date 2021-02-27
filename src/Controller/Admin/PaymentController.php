<?php

namespace App\Controller\Admin;

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
 * @Route("/admin/payment")
 */
class PaymentController extends AbstractController
{
    /**
     * @Route("/getlist", name="app_admin_payment_getlist")
     */
    public function getlist(PaymentRepository $paymentRepository, PaginatorInterface $paginator, Request $request, EntityManagerInterface $em): Response
    {
        $rows = $paymentRepository->findBy(array(), array('ID' => 'DESC'));

        $pagination = $paginator->paginate(
            $rows,
            $request->query->getInt('page', 1),
            $request->query->getInt('results', 15)
        );

        return $this->render('admin/payment/getlist.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/get/id/{id}", name="app_admin_payment_get")
     */
    public function show( int $id, PaymentRepository $paymentRepository, UserRepository $userRepository, EntityManagerInterface $em, \App\Service\UserServiceInterface $us): Response
    {
        $objRow = $paymentRepository->find($id);

        $us->updateBalance($objRow->getUserID());


        return $this->render('admin/payment/get.html.twig', [
            'row' => $objRow,
        ]);
    }

    /**
     * @Route("/add", name="app_admin_payment_add")
     */
    public function add(EntityManagerInterface $entityManager, Request $request, UserRepository $userRepository): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();
        $objUsers = $userRepository->getClients();
        $objRow = new Payment();

        if ($request->isMethod('POST')) {
            $objClient = $userRepository->find(intval($request->get('User_ID', 0)));

            if (!is_object($objClient)) {
                $this->addFlash('error', 'Выбранного клиента не существует');
            }

            if (!count($flashBag->peek('error'))) {
                $objRow->setName(trim($request->get('Name', '')));
                $objRow->setUserID($objClient->getId());
                $objRow->setUserName($objClient->getUsername());
                $objRow->setType(intval($request->get('Type', 0)));
                $objRow->setStatus(Payment::STATUS_NEW);
                $objRow->setTotal(floatval($request->get('Total', 0)));
                $objRow->setOrderID(0);
                $objRow->setChangedDate(new \DateTime());
                $objRow->setChangedBy($this->getUser()->getId());

                if (strlen($objRow->getName()) < 4) {
                    $this->addFlash('error', 'Заполните описание счёта');
                } else if (!array_key_exists($objRow->getType(), Payment::getTypes())) {
                    $this->addFlash('error', 'Выбранного счёта не существует');
                } else if ($objRow->getTotal() <= 0) {
                    $this->addFlash('error', 'Введите сумму счёта');
                }
            }

            if (!count($flashBag->peek('error'))) {
                $objRow->setFactor(Payment::getFactorByType($objRow->getType()));
            }

            if (!count($flashBag->peek('error'))) {
                $objRow->setCreatedDate(new \DateTime(date('Y-m-d H:i:s')));
                $objRow->setCreatedBy($this->getUser()->getId());

                $entityManager->persist($objRow);
                $entityManager->flush();
                $this->addFlash('success', 'Счёт успешно добавлен');

                return $this->redirectToRoute('app_admin_payment_get', ['id'=>$objRow->getId()]);
            }


        }

        return $this->render('admin/payment/add.html.twig', [
            'row' => $objRow,
            'Types' => Payment::getTypes(),
            'Clients' => $objUsers,
        ]);
    }

    /**
     * @Route("/edit/id/{id}", name="app_admin_payment_edit")
     */
    public function edit(int $id, EntityManagerInterface $entityManager, Request $request, UserRepository $userRepository, PaymentRepository $paymentRepository): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();

        $objRow = $paymentRepository->find($id);
        $objUsers = $userRepository->getClients();

        if (is_null($objRow)) {
            throw new \Exception("Счёт с ID = '$id' не найден", 404);
        } else if ($objRow->getStatus() == Payment::STATUS_PAID || $objRow->getStatus() == Payment::STATUS_CANCELLED) {
            throw new \Exception("Оплаченный или отменённый счёт нельзя редактировать", 400);
        }

        if ($request->isMethod('POST')) {
            $objClient = $userRepository->find(intval($request->get('User_ID', 0)));

            if (!is_object($objClient)) {
                $this->addFlash('error', 'Выбранного клиента не существует');
            }

            if (!count($flashBag->peek('error'))) {
                $objRow->setName(trim($request->get('Name', '')));
                $objRow->setUserID($objClient->getId());
                $objRow->setUserName($objClient->getUsername());
                $objRow->setType(intval($request->get('Type', 0)));
                $objRow->setTotal(floatval($request->get('Total', 0)));

                if (strlen($objRow->getName()) < 4) {
                    $this->addFlash('error', 'Заполните описание счёта');
                } else if (!array_key_exists($objRow->getType(), Payment::getTypes())) {
                    $this->addFlash('error', 'Выбранного счёта не существует');
                } else if ($objRow->getTotal() <= 0) {
                    $this->addFlash('error', 'Введите сумму счёта');
                }
            }

            if (!count($flashBag->peek('error'))) {
                $objRow->setFactor(Payment::getFactorByType($objRow->getType()));
            }

            if (!count($flashBag->peek('error'))) {
                $objRow->setChangedDate(new \DateTime(date('Y-m-d H:i:s')));
                $objRow->setChangedBy($this->getUser()->getId());

                $entityManager->flush();
                $this->addFlash('success', 'Счёт успешно изменён');

                return $this->redirectToRoute('app_admin_payment_get', ['id'=>$objRow->getId()]);
            }

        }

        return $this->render('admin/payment/edit.html.twig', [
            'row' => $objRow,
            'Types' => Payment::getTypes(),
            'Clients' => $objUsers,
        ]);
    }

    /**
     * @Route("/delete/id/{id}", name="app_admin_payment_delete")
     */
    public function delete(int $id, EntityManagerInterface $entityManager, PaymentRepository $paymentRepository, \App\Service\UserServiceInterface $us): Response
    {
        $objRow = $paymentRepository->find($id);
        
        $User_ID = $objRow->getUserID();

        if (is_null($objRow)) {
            throw new \Exception("Счёт с ID = '$id' не найден", 404);
        }

        $entityManager->remove($objRow);
        $entityManager->flush();
        
        $us->updateBalance($User_ID);
        
        $this->addFlash('success', 'Счёт успешно удалён');

        return $this->redirectToRoute('app_admin_payment_getlist');
    }

    /**
     * @Route("/change_status/id/{id}", name="app_admin_payment_change_status")
     */
    public function changeStatus(int $id, EntityManagerInterface $entityManager, Request $request, PaymentRepository $paymentRepository, \App\Service\UserServiceInterface $us): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();

        $objRow = $paymentRepository->find($id);

        if (is_null($objRow)) {
            throw new \Exception("Счёт с ID = '$id' не найден", 404);
        }

        if ($request->isMethod('POST')) {
            $objRow->setStatus(intval($request->get('Status', 0)));

            if (!array_key_exists($objRow->getStatus(), Payment::getStatuses())) {
                $this->addFlash('error', 'Неверный статус');
            }

            if (!count($flashBag->peek('error'))) {
                $objRow->setChangedDate(new \DateTime(date('Y-m-d H:i:s')));
                $objRow->setChangedBy($this->getUser()->getId());

                $entityManager->flush();
                
                $us->updateBalance($objRow->getUserID());
                
                $this->addFlash('success', 'Статус счёта успешно изменён');

                return $this->redirectToRoute('app_admin_payment_get', ['id'=>$objRow->getId()]);
            }

        }

        return $this->render('admin/payment/change-status.html.twig', [
            'row' => $objRow,
        ]);
    }

    /**
     * @Route("/ban/id/{id}", name="app_admin_user_ban")
     */
    public function ban( int $id, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $objRow = $userRepository->find($id);
        if (is_null($objRow)) {
            throw new \Exception("Пользователь с ID = '$id' не найден", 404);
        }

        $objRow->setRoles([]);
        $objRow->setActive(0);
        $em->flush();

        $this->addFlash('success', $objRow->getEmail().' заблокирован');
        return $this->redirectToRoute('app_admin_user_get', ['id'=>$objRow->getId()]);
    }
}
