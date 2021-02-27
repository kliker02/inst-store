<?php

namespace App\Service;

use App\Service\UserServiceInterface;
//use \Doctrine\ORM\EntityManagerInterface;
use App\Entity\Payment;

class UserService implements UserServiceInterface {

    /**
     *
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;
    
    /**
     *
     * @var \App\Repository\PaymentRepository
     */
    private $paymentRepository;
    
    /**
     *
     * @var \App\Repository\UserRepository
     */
    private $userRepository;

    public function __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Repository\PaymentRepository $pr, \App\Repository\UserRepository $ur) {
        $this->em = $em;
        $this->paymentRepository = $pr;
        $this->userRepository = $ur;  
    }

    public function updateBalance(int $User_ID) {
        $objClient = $this->userRepository->find($User_ID);

        if (is_null($objClient)) {
            //уведомляем тп
            return false;
        }
        
        $objInvoices = $this->paymentRepository->findBy(['User_ID' => $User_ID, 'Status'=> Payment::STATUS_PAID]);
        $total = 0;
        foreach ($objInvoices as $objInvoice) {
            $total += $objInvoice->getTotal()*$objInvoice->getFactor();
        }

        $objClient->setBalanceRUB($total);
        $this->em->flush();
        return true;
    }

}
