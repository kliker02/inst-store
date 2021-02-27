<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", name="app_admin")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('app_admin_payment_getlist');
    }
}
