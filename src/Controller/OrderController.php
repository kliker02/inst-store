<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Payment;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Service\UserServiceInterface;
use \Doctrine\ORM\EntityManagerInterface;
use http\Exception\BadUrlException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{

    /**
     * @Route("/client/order/getlist", name="app_client_order_getlist")
     */
    public function getlist(OrderRepository $orderRepository, PaginatorInterface $paginator, Request $request, EntityManagerInterface $entityManager, UserServiceInterface $us): Response
    {
        $objRows = $orderRepository->findBy(array('User_ID' => $this->getUser()->getId()), array('id' => 'DESC'));

        $pagination = $paginator->paginate(
            $objRows,
            $request->query->getInt('page', 1),
            $request->query->getInt('results', 15)
        );

        return $this->render('order/getlist.html.twig', [
            'pagination' => $pagination
        ]);

    }

    /**
     * @Route("/client/order/get/id/{id}", name="app_client_order_get")
     */
    public function show(int $id, OrderRepository $orderRepository, PaymentRepository $paymentRepository): Response
    {
        $objRow = $orderRepository->find($id);
        if ($this->getUser()->getId() != $objRow->getUserID()) {
            throw new BadUrlException('Неизвестный заказ');
        }

        $objPayments = $paymentRepository->findBy(['Order_ID' => $id]);

        if (!is_object($objRow)) {
            throw new \Exception('Заказ не найден', 404);
        }

        return $this->render('order/get.html.twig', [
            'row' => $objRow,
            'payments' => $objPayments
        ]);

    }


    /**
     * @Route("/client/order/new", name="app_client_order_new")
     */
    public function newOrder(ServiceRepository $serviceRepository, PaymentRepository $paymentRepository, UserRepository $userRepository, CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $entityManager, UserServiceInterface $userService): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();

        $Link = $request->get('Link', '');
        $Quantity = intval($request->get('Quantity', 0));

        $objServices = $serviceRepository->findAll();
        $objCategories = $categoryRepository->findAll();
        $objClient = $userRepository->find($this->getUser()->getId());

        if ($request->isMethod('POST')) {
            $Service_ID = $request->get('Service_ID', 0);
            $Total = 0;
            if (!$Service_ID) {
                $this->addFlash('error', 'Выберите услугу');
            } else {
                $objService = $serviceRepository->find($Service_ID);
                if (!is_object($objService)) {
                    $this->addFlash('error', 'Выбранная услуга не найдена');
                }
                $Total = $objService->getPrice() - $objService->getPrice() * ($objClient->getDiscount()/100);
                $Total *= $Quantity/1000;
            }

            if (strlen($Link) < 4) {
                $this->addFlash('error', 'Укажите ссылку для накрутки');
            }

            if (!count($flashBag->peek('error'))) {
                if ($Quantity > $objService->getMaxQuantity() || $Quantity < $objService->getMinQuantity()) {
                    $this->addFlash('error', 'Укажите верное количество накрутки');
                } else if ($Total > $objClient->getBalanceRUB()) {
                    $this->addFlash('error', 'Недостаточно средств');
                }
            }

            if (!count($flashBag->peek('error'))) {
                if(!empty($objService->getAPI())) {
                    $URL = str_replace('[QUANTITY]', $Quantity, $objService->getAPI());
                    $URL = str_replace('[LINK]', $Link, $URL);

                    if(isset($additional) && !empty($additional)) {
                        $URL = str_replace('[ADDON]', $additional, $URL);
                    }
                    $return = $this->sendCurl($URL);
                    $resp = json_decode($return);
                    $this->writeLogServiceRequest('Time: '. date('d.m.Y H:i:s') . ' ' . $return);

                    if(isset($resp) && property_exists($resp, 'Error')) {
                        $this->writeLogServiceRequest('Time: '. date('d.m.Y H:i:s') . ' Error');
                    }

                    $API_ID = '';
                    if(isset($resp) && property_exists($resp, 'order'))
                        $API_ID = $resp->order;

                    //save order
                    $objRow = new Order();
                    $objRow->setUserID($this->getUser()->getId());
                    $objRow->setUserName($this->getUser()->getUsername());
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


                    $userService->updateBalance($objClient->getId());


                    $this->addFlash('success', 'Заказ успешно принят в обработку');
                    $this->redirectToRoute('app_client_order_getlist');
                }
            }
        }

        return $this->render('order/new.html.twig', [
            'Services' => $objServices,
            'Categories' => $objCategories,
            'Link' => $Link,
            'Quantity' => $Quantity,
        ]);
    }
    
    /**
     * @Route("/order/fast", name="app_order_fast")
     */
    public function fastOrder(ServiceRepository $serviceRepository, PaymentRepository $paymentRepository, UserRepository $userRepository, CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $entityManager, UserServiceInterface $userService): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();

        $Link = $request->get('Link', '');
        $Quantity = intval($request->get('Quantity', 0));
        $Email = strval($request->get('Email', ''));

        $objServices = $serviceRepository->findAll();
        $objCategories = $categoryRepository->findAll();

        if ($request->isMethod('POST')) {
            $Service_ID = $request->get('Service_ID', 0);
            $Total = 0;
            if (!$Service_ID) {
                $this->addFlash('error', 'Выберите услугу');
            } else {
                $objService = $serviceRepository->find($Service_ID);
                if (!is_object($objService)) {
                    $this->addFlash('error', 'Выбранная услуга не найдена');
                } else if (strlen($objService->getAPI()) < 4) {
                    $this->addFlash('error', 'Выбранная услуга не работает. Выберите другую');
                }
                $Total = $objService->getPrice();
                $Total *= $Quantity/1000;
                $Total = round($Total*100)/100;
            }

            if (strlen($Link) < 4) {
                $this->addFlash('error', 'Укажите ссылку для накрутки');
            } else if (!preg_match('/[:\/\/]/', $Link)) {
                $this->addFlash('error', 'Неверная ссылка для накрутки. Требуется вставить прямую ссылку, например: https://instagram.com/eminem');
            }

            if (!count($flashBag->peek('error'))) {
                if ($Quantity > $objService->getMaxQuantity() || $Quantity < $objService->getMinQuantity()) {
                    $this->addFlash('error', 'Укажите верное количество накрутки');
                } else if (strlen($Email) < 3) {
                    $this->addFlash('error', '3аполните Email для обратной связи');
                } else if ($Total < 1) {
                    $this->addFlash('error', 'Для создания быстрого заказа сумма покупки должна быть больше 1руб.');
                }
            }

            if (!count($flashBag->peek('error'))) {
                if(!empty($objService->getAPI())) {
                    
                    //save order
                    $objRow = new Order();
                    $objRow->setUserID(1);
                    $objRow->setUserName($Email);
                    $objRow->setService($objService);
                    $objRow->setQuantity($Quantity);
                    $objRow->setStatus(Order::STATUS_BOOKED);
                    $objRow->setAPIID(0);
                    $objRow->setTotal($Total);
                    $objRow->setLink($Link);
                    $objRow->setCreatedDate(new \DateTime(date('Y-m-d H:i:s')));
                    $entityManager->persist($objRow);
                    $entityManager->flush();

                    $objInvoice = new Payment();
                    $objInvoice->setTotal($Total);
                    $objInvoice->setOrderID($objRow->getId());
                    $objInvoice->setStatus(Payment::STATUS_NEW);
                    $objInvoice->setType(Payment::TYPE_ORDER);
                    $objInvoice->setFactor(Payment::getFactorByType(Payment::TYPE_ORDER));
                    $objInvoice->setName('Оплата заказа #'. $objRow->getId());
                    $objInvoice->setUserID(1);
                    $objInvoice->setUserName($Email);
                    $objInvoice->setTotal($Total);
                    $objInvoice->setChangedDate(new \DateTime());
                    $objInvoice->setChangedBy(1);
                    $objInvoice->setCreatedDate(new \DateTime(date('Y-m-d H:i:s')));
                    $objInvoice->setCreatedBy(1);
                    $entityManager->persist($objInvoice);
                    $entityManager->flush();
                    
                    
                    $urlParams = '?account=1&desc=Пополнение счёта&sum='.$Total;
                    $url = 'https://unitpay.money/pay/354591-b57f7/card'.$urlParams;
                    return $this->redirect($url);
                }
            }
        }

        return $this->render('order/new-fast.html.twig', [
            'Services' => $objServices,
            'Categories' => $objCategories,
            'Link' => $Link,
            'Quantity' => $Quantity,
            'Email' => $Email,
        ]);
    }

    /**
     * @Route("/order/check-status", name="app_order_check_status")
     */
    public function checkStatus(UserRepository $userRepository, OrderRepository $orderRepository, ServiceRepository $serviceRepository, PaymentRepository $paymentRepository, EntityManagerInterface $entityManager, UserServiceInterface $userService)
    {
        $objRows = $orderRepository->findBy(['Status' => ['Pending', 'Processing', 'In progress']]);

        foreach ($objRows as $objRow) {
            if (!$objRow->getAPIID()) continue;
            $objService = $serviceRepository->find($objRow->getService()->getId());
            $CompleteURL = str_replace('[OrderID]', $objRow->getAPIID(), $objService->getOrderAPI());
            $return = $this->sendCurl($CompleteURL);
            $response = json_decode($return);
            $remains = intval($response->remains);

            $objPayment = $paymentRepository->findOneBy(['Order_ID' => $objRow->getId()]);

            $this->writeLogServiceRequest('Time: '. date('d.m.Y H:i:s') . ' ' . $return);

            $objRow->setQuantityRemains($remains);

            $status = '';
            if (isset($response) && property_exists($response, 'status')) {
                $status = $response->status;
            }

            if ($status == Order::STATUS_CANCELED) {
                $objRow->setStatus('Cancelled');
                $objRow->setStartCount($response->start_count);

                $objPayment->setStatus(Payment::STATUS_CANCELLED);

                $entityManager->flush();
                
            } else if ($status == Order::STATUS_PARTIAL) {
                $refund = $objPayment->getTotal() / $objRow->getQuantity() * $remains;

                $objRefund = new Payment();
                $objRefund->setTotal($refund);
                $objRefund->setOrderID($objRow->getId());
                $objRefund->setStatus(Payment::STATUS_PAID);
                $objRefund->setType(Payment::TYPE_DEBIT);
                $objRefund->setFactor(Payment::getFactorByType(Payment::TYPE_DEBIT));
                $objRefund->setName('Возврат с частично выполненого заказа');
                $objRefund->setUserID($objRow->getUserID());
                $objRefund->setUserName($objRow->getUserName());
                $objRefund->setChangedDate(new \DateTime());
                $objRefund->setChangedBy($this->getUser()->getId());
                $objRefund->setCreatedDate(new \DateTime(date('Y-m-d H:i:s')));
                $objRefund->setCreatedBy($this->getUser()->getId());
                $entityManager->persist($objRefund);

                $objRow->setStatus(Order::STATUS_PARTIAL);
                $objRow->setStartCount($response->start_count);

                $entityManager->flush();
            } else {
                $objRow->setStatus($status);
                $objRow->setStartCount($response->start_count);
                $entityManager->flush();
            }
            
            $userService->updateBalance($objRow->getUserID());
            
        }

        return new JsonResponse(array());
    }
    
    /**
     * @Route("/order/check-fast", name="app_order_check_fast")
     */
    public function checkFast(UserRepository $userRepository, OrderRepository $orderRepository, ServiceRepository $serviceRepository, PaymentRepository $paymentRepository, EntityManagerInterface $entityManager, UserServiceInterface $userService)
    {
        $objRows = $orderRepository->findBy(['Status' => [Order::STATUS_BOOKED]]);

        foreach ($objRows as $objRow) {
            $objInvoice = $paymentRepository->findOneBy(['Status' => Payment::STATUS_PAID, 'Order_ID' => $objRow->getId()]);
            if (!is_object($objInvoice)) continue;
            
            if(!empty($objRow->getService()->getAPI())) {
                $objService = $objRow->getService();
                
                $URL = str_replace('[QUANTITY]', $objRow->getQuantity(), $objService->getAPI());
                $URL = str_replace('[LINK]', $objRow->getLink(), $URL);

                if(isset($additional) && !empty($additional)) {
                    $URL = str_replace('[ADDON]', $additional, $URL);
                }
                $return = $this->sendCurl($URL);
                $resp = json_decode($return);
                $this->writeLogServiceRequest('Time: '. date('d.m.Y H:i:s') . ' ' . $return);

                if(isset($resp) && property_exists($resp, 'Error')) {
                    $this->writeLogServiceRequest('Time: '. date('d.m.Y H:i:s') . ' Error');
                }
                
                $objRow->setStatus(Order::STATUS_PENDING);
                if(isset($resp) && property_exists($resp, 'order')) $objRow->setAPIID ($resp->order);
                
                $entityManager->flush();
            }
           
        }

        return new JsonResponse(array());
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
