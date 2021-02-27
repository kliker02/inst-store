<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\CategoryRepository;
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
 * @Route("/admin/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/getlist", name="app_admin_user_getlist")
     */
    public function getlist(UserRepository $userRepository, PaginatorInterface $paginator, Request $request, EntityManagerInterface $em): Response
    {
        $rows = $userRepository->findBy(array(), array('id' => 'DESC'));

        $pagination = $paginator->paginate(
            $rows,
            $request->query->getInt('page', 1),
            $request->query->getInt('results', 15)
        );

        return $this->render('admin/user/getlist.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/get/id/{id}", name="app_admin_user_get")
     */
    public function show( int $id, UserRepository $userRepository): Response
    {
        $objRow = $userRepository->find($id);


        return $this->render('admin/user/get.html.twig', [
            'row' => $objRow,
        ]);
    }

    /**
     * @Route("/add", name="app_admin_user_add")
     */
    public function add(EntityManagerInterface $entityManager, Request $request, CategoryRepository $categoryRepository): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();

        $objRow = new Service();

        if ($request->isMethod('POST')) {
            $Category_ID = intval($request->get('CategoryID', 0));
            $objCategory = $categoryRepository->find($Category_ID);

            $objRow->setName(trim($request->get('Name', '')));
            $objRow->setDescription(trim($request->get('Description', '')));
            $objRow->setPrice(floatval($request->get('Price', 0)));
            $objRow->setMinQuantity(intval($request->get('MinQuantity', 0)));
            $objRow->setMaxQuantity(intval($request->get('MaxQuantity', 0)));
            $objRow->setCategory($objCategory, 0);
            $objRow->setType('Default');
            $objRow->setStatus(intval($request->get('Status', null)));
            $objRow->setAPI(trim($request->get('API', '')));
            $objRow->setOrderAPI(trim($request->get('CheckOrderStatus', '')));
            $objRow->setResellerPrice(floatval($request->get('ResellerPrice', 0)));
            $objRow->setCreatedDate(new \DateTime(date('Y-m-d H:i:s')));


            if (strlen($objRow->getName()) < 4) {
                $this->addFlash('error', 'Введите название услуги');
            } else if (!is_object($objRow->getCategory())) {
                $this->addFlash('error', 'Выберите категорию для услуги');
            } else if (strlen($objRow->getDescription()) < 10) {
                $this->addFlash('error', 'Введите описание услуги');
            } else if (is_null($objRow->getStatus())) {
                $this->addFlash('error', 'Выберите корректный статус');
            } else if ($objRow->getPrice() == 0) {
                $this->addFlash('error', 'Введите цену услуги');
            } else if (strlen($objRow->getAPI()) < 4) {
                $this->addFlash('error', 'Введите API адрес запроса');
            } else if (strlen($objRow->getOrderAPI()) < 4) {
                $this->addFlash('error', 'Введите API адрес запроса проверки статуса заказа');
            } else if ($objRow->getMinQuantity() == 0) {
                $this->addFlash('error', 'Введите минимальное кол-во накрутки по услуге');
            } else if ($objRow->getMinQuantity() >= $objRow->getMaxQuantity()) {
                $this->addFlash('error', 'Минимальное кол-во накрутки по услуге должно быть меньше, чем максимальное');
            }

            if (!count($flashBag->peek('error'))) {
                $entityManager->persist($objRow);
                $entityManager->flush();
                $this->addFlash('success', 'Услуга "' . $objRow->getName() . '" успешно добавлена');

                return $this->redirectToRoute('app_admin_service_getlist');
            }


        }

        return $this->render('admin/service/add.html.twig', [
            'Statuses' => Service::getStatuses(),
            'Categories' => $categoryRepository->findAll(),
            'row' => $objRow,
        ]);
    }

    /**
     * @Route("/edit/id/{id}", name="app_admin_user_edit")
     */
    public function edit($id, EntityManagerInterface $entityManager, Request $request, CategoryRepository $categoryRepository, UserRepository $userRepository): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();

        $objRow = $userRepository->find($id);

        if (is_null($objRow)) {
            throw new \Exception("Пользователь с ID = '$id' не найден", 404);
        }

        if ($request->isMethod('POST')) {
            $objRow->setEmail(trim($request->get('Email', '')));
            $objRow->setDiscount(floatval($request->get('Discount', 0)));

            $isAdmin = intval($request->get('isAdmin', 0));
            if ($isAdmin) {
                $objRow->setRoles(array(User::ROLE_ADMIN));
            }

            if (strlen($objRow->getEmail()) < 4) {
                $this->addFlash('error', 'Введите адрес электронной почты');
            } else if ($objRow->getDiscount() > 100) {
                $this->addFlash('error', 'Скидка не может быть больше 100%');
            }

            if (!count($flashBag->peek('error'))) {
                $entityManager->flush();
                $this->addFlash('success', 'Пользователь отредактирован');

                return $this->redirectToRoute('app_admin_user_get', ['id'=>$objRow->getId()]);
            }


        }

        return $this->render('admin/user/edit.html.twig', [
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

    /**
     * @Route("/unban/id/{id}", name="app_admin_user_unban")
     */
    public function unban( int $id, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $objRow = $userRepository->find($id);
        if (is_null($objRow)) {
            throw new \Exception("Пользователь с ID = '$id' не найден", 404);
        }

        $objRow->setActive(1);
        $em->flush();

        $this->addFlash('success', $objRow->getEmail().' разблокирован');
        return $this->redirectToRoute('app_admin_user_get', ['id'=>$objRow->getId()]);
    }
}
