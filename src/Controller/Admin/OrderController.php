<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\Service;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
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
 * @Route("/admin/order")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/getlist", name="app_admin_order_getlist")
     */
    public function getlist(OrderRepository $orderRepository, PaginatorInterface $paginator, Request $request, EntityManagerInterface $entityManager): Response
    {
        $objRows = $orderRepository->findBy(array(), array('id' => 'DESC'));

        $pagination = $paginator->paginate(
            $objRows,
            $request->query->getInt('page', 1),
            $request->query->getInt('results', 15)
        );

        return $this->render('admin/order/getlist.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/add", name="app_admin_order_add")
     */
    public function add(ServiceRepository $serviceRepository, PaymentRepository $paymentRepository, UserRepository $userRepository, CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $entityManager, \App\Service\UserServiceInterface $us): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();

        $objServices = $serviceRepository->findAll();
        $objCategories = $categoryRepository->findAll();
        $objUsers = $userRepository->getClients();
        $objRow = new Order();

        $Link = $request->get('Link', '');
        $Quantity = intval($request->get('Quantity', 0));

        if ($request->isMethod('POST')) {
            $Service_ID = $request->get('Service_ID', 0);
            $Total = 0;

            $User_ID = intval($request->get('User_ID', 0));
            $objClient = $userRepository->find($User_ID);

            if (!is_object($objClient)) {
                $this->addFlash('error', 'Пользователь не найден в базе данных');
            }
            if (!count($flashBag->peek('error'))) {
                if (!$Service_ID) {
                    $this->addFlash('error', 'Выберите услугу');
                } else {
                    $objService = $serviceRepository->find($Service_ID);
                    if (!is_object($objService)) {
                        $this->addFlash('error', 'Выбранная услуга не найдена');
                    }
                    $Total = $objService->getPrice() - $objService->getPrice() * ($objClient->getDiscount() / 100);
                    $Total *= $Quantity / 1000;
                }
            }

            if (strlen($Link) < 4) {
                $this->addFlash('error', 'Укажите ссылку для накрутки');
            }

            if (!count($flashBag->peek('error'))) {
                if ($Quantity > $objService->getMaxQuantity() || $Quantity < $objService->getMinQuantity()) {
                    $this->addFlash('error', 'Укажите верное количество накрутки');
                } else if ($Total > $objClient->getBalanceRUB()) {
                    /* админ может создать заказ даже с отрицательным балансом
                    $this->addFlash('error', 'Недостаточно средств');*/
                }
            }

            if (!count($flashBag->peek('error'))) {
                if (!empty($objService->getAPI())) {
                    $URL = str_replace('[QUANTITY]', $Quantity, $objService->getAPI());
                    $URL = str_replace('[LINK]', $Link, $URL);

                    if (isset($additional) && !empty($additional)) {
                        $URL = str_replace('[ADDON]', $additional, $URL);
                    }

                    $return = $this->sendCurl($URL);

                    $resp = json_decode($return);
                    $this->writeLogServiceRequest('Time: ' . date('d.m.Y H:i:s') . ' ' . $return);

                    if (isset($resp) && property_exists($resp, 'Error')) {
                        $this->writeLogServiceRequest('Time: ' . date('d.m.Y H:i:s') . ' Error');
                    }

                    $API_ID = '';
                    if (isset($resp) && property_exists($resp, 'order'))
                        $API_ID = $resp->order;

                    if (strlen($API_ID)) {
                        //save order
                        $objRow->setUserID($objClient->getId());
                        $objRow->setUserName($objClient->getUsername());
                        $objRow->setService($objService);
                        $objRow->setQuantity($Quantity);
                        $objRow->setStatus('Pending');
                        $objRow->setAPIID($API_ID);
                        $objRow->setTotal($Total);
                        $objRow->setLink($Link);
                        $objRow->setCreatedDate(new \DateTime(date('Y-m-d H:i:s')));
                        $entityManager->persist($objRow);
                        $entityManager->flush();

                        $objInvoice = new Payment();
                        $objInvoice->setTotal($Total);
                        $objInvoice->setOrderID($objRow->getId());
                        $objInvoice->setStatus(Payment::STATUS_PAID);
                        $objInvoice->setType(Payment::TYPE_ORDER);
                        $objInvoice->setFactor(Payment::getFactorByType(Payment::TYPE_ORDER));
                        $objInvoice->setName('Оплата заказа');
                        $objInvoice->setUserID($objClient->getId());
                        $objInvoice->setUserName($objClient->getUsername());
                        $objInvoice->setTotal($Total);
                        $objInvoice->setChangedDate(new \DateTime());
                        $objInvoice->setChangedBy($this->getUser()->getId());
                        $objInvoice->setCreatedDate(new \DateTime(date('Y-m-d H:i:s')));
                        $objInvoice->setCreatedBy($this->getUser()->getId());
                        $entityManager->persist($objInvoice);
                        $entityManager->flush();


                        $us->updateBalance($objClient->getId());


                        $this->addFlash('success', 'Заказ успешно принят в обработку');
                        return $this->redirectToRoute('app_admin_order_getlist');
                    } else {
                        $this->addFlash('error', 'Заказ не был отправлен на выполнение. Обратитесь в техподдержку');
                    }
                }
            }
        }

        return $this->render('admin/order/add.html.twig', [
            'Services' => $objServices,
            'Categories' => $objCategories,
            'Link' => $Link,
            'Quantity' => $Quantity,
            'Clients' => $objUsers,
            'row' => $objRow,

        ]);
    }

    public function SendCurl($URL, $return = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);

        curl_close($ch);

        if ($return == true) return $result;
    }

    public function writeLogServiceRequest($text) {
        $fp = fopen($this->getParameter('kernel.project_dir').'/logs/service_request.txt', 'a');//opens file in append mode
        fwrite($fp, $text . " \n");
        fclose($fp);
    }
}
