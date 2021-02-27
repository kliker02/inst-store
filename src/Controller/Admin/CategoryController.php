<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin/category")
 */
class CategoryController extends AbstractController
{

    /**
     * @Route("/getlist", name="app_admin_category_getlist")
     */
    public function getlist(CategoryRepository $categoryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $objRows = $categoryRepository->findAll();

        $pagination = $paginator->paginate(
            $objRows,
            $request->query->getInt('page', 1),
            $request->query->getInt('results', 15)
        );


        return $this->render('admin/category/getlist.html.twig', [
            'pagination' => $pagination,
            'Category'   => new Category()
        ]);
    }

    /**
     * @Route("/add", name="app_admin_category_add")
     */
    public function add(EntityManagerInterface $entityManager, Request $request): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();
        $objRow = new Category();

        if ($request->isMethod('POST')) {
            $objRow->setName(trim($request->get('Title', '')));
            $objRow->setDescription(trim($request->get('Description', '')));
            $objRow->setStatus(intval($request->get('Status', null)));
            $objRow->setCreateDate(new \DateTime(date('Y-m-d H:i:s')));

            if (!strlen($objRow->getName())) {
                $this->addFlash('error', 'Введите название категории');
            } else if (!strlen($objRow->getDescription())) {
                $this->addFlash('error', 'Введите описание категории');
            } else if (is_null($objRow->getStatus())) {
                $this->addFlash('error', 'Выберите корректный статус');
            }

            if (!count($flashBag->peek('error'))) {
                $entityManager->persist($objRow);
                $entityManager->flush();
                $this->addFlash('success', 'Категория "'. $objRow->getName() .'" успешно создана');

                return $this->redirectToRoute('app_admin_category_getlist');
            }
        }

        return $this->render('admin/category/add.html.twig', [
            'Statuses' => Category::getStatuses(),
            'row' => $objRow,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="app_admin_category_edit")
     */
    public function edit(int $id, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, Request $request): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();

        $objRow = $categoryRepository->find($id);
        if (is_null($objRow)) throw new \InvalidArgumentException("Категории с ID = '$id' нет в базе данных", 404);

        if ($request->isMethod('POST')) {
            $objRow->setName(trim($request->get('Title', '')));
            $objRow->setDescription(trim($request->get('Description', '')));
            $objRow->setStatus(intval($request->get('Status', null)));
            $objRow->setCreateDate(new \DateTime(date('Y-m-d H:i:s')));

            if (!strlen($objRow->getName())) {
                $this->addFlash('error', 'Введите название категории');
            } else if (!strlen($objRow->getDescription())) {
                $this->addFlash('error', 'Введите описание категории');
            } else if (is_null($objRow->getStatus())) {
                $this->addFlash('error', 'Выберите корректный статус');
            }

            if (!count($flashBag->peek('error'))) {
                $entityManager->flush();
                $this->addFlash('success', 'Категория "'. $objRow->getName() .'" успешно изменена');

                return $this->redirectToRoute('app_admin_category_getlist');
            }
        }

        return $this->render('admin/category/edit.html.twig', [
            'Statuses' => Category::getStatuses(),
            'row' => $objRow,
        ]);
    }

    /**
     * @Route("/delete/id/{id}", name="app_admin_category_delete")
     */
    public function delete( int $id, CategoryRepository $categoryRepository, EntityManagerInterface $em)
    {
        $objRow = $categoryRepository->find($id);
        if (is_null($objRow)) {
            throw new \Exception("Категория с ID = '$id' не найдена", 404);
        }

        $em->remove($objRow);
        $em->flush();

        $this->addFlash('success', 'Категория успешно удалена');
        return $this->redirectToRoute('app_admin_category_getlist');
    }
}
