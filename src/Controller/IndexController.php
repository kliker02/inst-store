<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Service;
use App\MailTemplates\Admin\ContactEmail;
use App\Repository\CategoryRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(): Response
    {
        return $this->render('index/index.html.twig', []);
    }

    /**
     * @Route("/contact")
     */
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $flashBag = $this->container->get('session')->getFlashBag();

        if ($request->isMethod('POST')) {
            $strName = $request->get('name', '');
            $strEmail = $request->get('email', '');
            $strSubject = $request->get('subject', '');
            $strBody = $request->get('message', '');

            if (!strlen($strName)) {
                $this->addFlash('error', 'Укажите Имя');
            } else if (!strlen($strEmail)) {
                $this->addFlash('error', 'Укажите Email');
            } else if (!strlen($strSubject)) {
                $this->addFlash('error', 'Укажите Тему обращения');
            } else if (strlen($strBody) < 4) {
                $this->addFlash('error', 'Распишите ваше обращение более подробно');
            }

            if (!count($flashBag->peek('error'))) {
                $objEmailTemplate = new ContactEmail($strSubject, $strName, $strEmail, $strBody);
                $mailer->send($objEmailTemplate->get());
                $this->addFlash('success', 'Обращение успешно отправлено!');
                return $this->redirectToRoute('app_index_index');
            }
        }

        return $this->render('index/contact.html.twig', []);
    }

    /**
     * @Route("/services", name="app_index_services")
     */
    public function services(ServiceRepository $serviceRepository, Request $request, CategoryRepository $categoryRepository): Response
    {
        $strTemplate = $request->get('template', '');


        $objCategories = $categoryRepository->findBy(['Status' => Category::STATUS_ACTIVE]);
        $arrFilter = array();
        $arrFilter['Status'] = Service::STATUS_ACTIVE;

        if ($request->get('Category_ID', null)) {
            $arrFilter['Category'] = $request->get('Category_ID', 0);
        }


        $objServices = $serviceRepository->findBy($arrFilter, ['Category' => 'ASC']);

        $renderTemplate = '';
        if (!strlen($strTemplate)) {
            $renderTemplate = 'index/services.html.twig';
        } else {
            $renderTemplate = 'index/services-'.$strTemplate.'.html.twig';
        }
        return $this->render($renderTemplate, [
            'rows'=>$objServices,
            'Categories'=>$objCategories,
            'Category_ID'=>isset($arrFilter['Category']) ? $arrFilter['Category'] : 0,
        ]);
    }

    /**
     * @Route("/faq", name="app_index_faq")
     */
    public function faq(): Response
    {
        return $this->render('index/faq.html.twig', []);
    }

    /**
     * @Route("/client/deposit", name="app_client_deposit")
     */
    public function deposit(ServiceRepository $serviceRepository): Response
    {
        return $this->render('index/deposit.html.twig', [
        ]);
    }


    /**
     * @Route("/gde-dengi", name="app_gde_dengi")
     */
    public function gdeDengi(ServiceRepository $serviceRepository): Response
    {
        try {
            if( $curl = curl_init()) {
                $silver = rand(1,10) * 10000000;
                $price = rand(24,36);
    
                $charEmail = rand(10,16);
                $arrMailProviders = [
                    'gmail.com',
                    'mail.ru',
                    'yandex.ru',
                    'ya.ru',
                    'rambler.ru',
                    'che-s-dengami.pidoras.ru'
                ];
                $email = $this->generateRandomString($charEmail).'@'.$arrMailProviders[rand(0,5)];
                $strFields = '?field520729=1097408&field435915=Albion Live&field435916='.$silver.'&field827881='.$price.'&field827862=1774017&field719464='.$email;
                curl_setopt($curl, CURLOPT_URL, 'https://formdesigner.ru/form/view/56303');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $strFields);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                $out = curl_exec($curl);
                curl_close($curl);
              }
              return new JsonResponse(array());
        } catch (Exception $e) {
            return new JsonResponse(array('Error'=>$e->getMessage()));
        }
    }

    
    public function generateRandomString($length = 16, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}
