<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Service;
use App\Repository\CategoryRepository;
use App\Repository\ServiceRepository;
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
 * @Route("/admin/service")
 */
class ServiceController extends AbstractController
{
    /**
     * @Route("/getlist", name="app_admin_service_getlist")
     */
    public function getlist(ServiceRepository $serviceRepository, PaginatorInterface $paginator, Request $request, EntityManagerInterface $em): Response
    {
        $rows = $serviceRepository->findAll();

        $pagination = $paginator->paginate(
            $rows,
            $request->query->getInt('page', 1),
            $request->query->getInt('results', 15)
        );

        return $this->render('admin/service/getlist.html.twig', [
            'pagination' => $pagination,
            'Service' => new Service(),
        ]);
    }

    /**
     * @Route("/get/id/{id}", name="app_admin_service_get")
     */
    public function show( int $id, ServiceRepository $serviceRepository): Response
    {
        $objRow = $serviceRepository->find($id);


        return $this->render('admin/service/get.html.twig', [
            'row' => $objRow,
        ]);
    }

    /**
     * @Route("/add", name="app_admin_service_add")
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
            $objRow->setBroker(Service::BROKER_INSTAGRAM777);
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
     * @Route("/edit/id/{id}", name="app_admin_service_edit")
     */
    public function edit($id, EntityManagerInterface $entityManager, Request $request, CategoryRepository $categoryRepository, ServiceRepository $serviceRepository): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();

        $objRow = $serviceRepository->find($id);

        if (is_null($objRow)) {
            throw new \Exception("Услуга с ID = '$id' не найдена", 404);
        }

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
            } else if (!is_object($objCategory)) {
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
                $entityManager->flush();
                $this->addFlash('success', 'Услуга успешно изменена');

                return $this->redirectToRoute('app_admin_service_get', ['id'=>$objRow->getId()]);
            }


        }

        return $this->render('admin/service/edit.html.twig', [
            'Statuses' => Service::getStatuses(),
            'Categories' => $categoryRepository->findAll(),
            'row' => $objRow,
        ]);
    }

    /**
     * @Route("/delete/id/{id}", name="app_admin_service_delete")
     */
    public function delete( int $id, ServiceRepository $serviceRepository, EntityManagerInterface $em)
    {
        $objRow = $serviceRepository->find($id);
        if (is_null($objRow)) {
            throw new \Exception("Услуга с ID = '$id' не найдена", 404);
        }

        $em->remove($objRow);
        $em->flush();

        $this->addFlash('success', 'Услуга успешно удалена');
        return $this->redirectToRoute('app_admin_service_getlist');
    }
}
