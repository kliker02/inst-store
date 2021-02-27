<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Payment;
use App\Entity\Service;
use App\Repository\PaymentRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnitpayController extends AbstractController
{
    /**
     * @Route("/unitpay/handler")
     */
    public function handler(PaymentRepository $paymentRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request,  \App\Service\UserServiceInterface $us): ?Response
    {
        if (!isset($_GET['method']) || !isset($_GET['params'])) return new JsonResponse([]);
        list($method, $params) = array($_GET['method'], $_GET['params']);

        $updateBalance = 0;
        switch ($method) {
            // Just check order (check server status, check order in DB and etc)
            case 'check':
                $updateBalance = 1;
                $objClient = $userRepository->find($params['account']);
                if (!is_object($objClient)) {
                    //добавляем в логи
                    return new JsonResponse(['error' => ['message' => 'Такого клиента не существует']]);
                }
                    
                $objPayment = new Payment();
                $objPayment->setExternalGUID($params['unitpayId']);
                $objPayment->setName(trim($request->get('Name', 'Пополнение баланса')));
                $objPayment->setUserID($objClient->getId());
                $objPayment->setUserName($objClient->getUsername());
                $objPayment->setType(Payment::TYPE_DEBIT);
                $objPayment->setStatus(Payment::STATUS_NEW);
                $objPayment->setTotal($params['profit']);
                $objPayment->setChangedDate(new \DateTime());
                $objPayment->setOrderID(0);
                $objPayment->setChangedBy(0);
                $objPayment->setFactor(Payment::getFactorByType($objPayment->getType()));
                $objPayment->setCreatedDate(new \DateTime(date('Y-m-d H:i:s')));
                $objPayment->setCreatedBy(1);

                $entityManager->persist($objPayment);
                $entityManager->flush();
                break;

            case 'pay':
                $objPayment = $paymentRepository->findOneBy(['ExternalGUID' => $params['unitpayId']]);
                if (!is_object($objPayment)) {
                    // сообщаем тп
                    return null;
                }
                $objPayment->setStatus(Payment::STATUS_PAID);
                $entityManager->flush();
                
                
                if ($objPayment->getUserID() == 1) {
                    $objPayment = $paymentRepository->findOneBy(['Total' => $objPayment->getTotal(), 'Status' => Payment::STATUS_NEW, 'Type' => Payment::TYPE_ORDER]);
                    if (is_object($objPayment)) {
                        $objPayment->setStatus(Payment::STATUS_PAID);
                        $entityManager->flush();
                    }
                }
                
                $us->updateBalance($objPayment->getUserID());
                break;
            case 'error':
                file_put_contents("payment.log",'Error logged!');
                break;
            case 'refund':
                // Please cancel the order
                $objPayment = $paymentRepository->findOneBy(['ExternalGUID' => $params['unitpayId']]);
                if (is_object($objPayment)) {
                    $objPayment->setStatus(Payment::STATUS_CANCELLED);
                    $entityManager->flush();
                    
                    if ($objPayment->getUserID() == 1) {
                        $objPayment = $paymentRepository->findOneBy(['Total' => $objPayment->getTotal(), 'Status' => Payment::STATUS_PAID, 'Type' => Payment::TYPE_ORDER]);
                        if (is_object($objPayment)) {
                            $objPayment->setStatus(Payment::STATUS_CANCELLED);
                            $entityManager->flush();
                        }
                    }
                    
                    $us->updateBalance($objPayment->getUserID());
                }

                break;
        }

        return new JsonResponse(['result' => ['message' => 'Запрос успешно обработан']]);
    }
}
