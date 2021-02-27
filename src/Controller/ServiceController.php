<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Service;
use App\Repository\CategoryRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends AbstractController
{
    protected $KeyInstagram777 = '41ea98cdfff5f6b60fec16a374b36127';
    protected $UrlServicesInstagram777 = 'https://instagram777.ru/api/?action=services&key=';
    protected $UrlFullServicesInstagram777 = 'https://instagram777.ru/api/?action=services_full&key=';

    //instagram777 api requests
    protected $API_REQUEST777 = 'https://instagram777.ru/api/?key=41ea98cdfff5f6b60fec16a374b36127&action=create&service=[SERVICE_ID]&quantity=[QUANTITY]&link=[LINK]';
    protected $CHECKSTATUS_REQUEST777 = 'https://instagram777.ru/api/?key=41ea98cdfff5f6b60fec16a374b36127&action=status&order=[OrderID]';

    protected $markup = 0.15; // 15%

    /**
     * @Route("/service/get/id/{id}", name="app_service_get")
     */
    public function index(int $id, ServiceRepository $serviceRepository, Request $request): Response
    {
        $strTemplate = $request->get('template', '');

        $arrFilter = array();
        $arrFilter['Status'] = Service::STATUS_ACTIVE;
        $arrFilter['id'] = $id;

        $objRow = $serviceRepository->findOneBy($arrFilter);
        if (is_null($objRow)) {
            throw new \Exception('Не найдена услуга', 404);
        }

        $renderTemplate = '';
        if (!strlen($strTemplate)) {
            $renderTemplate = 'service/get.html.twig';
        } else {
            $renderTemplate = 'service/get-'.$strTemplate.'.html.twig';
        }
        return $this->render($renderTemplate, [
            'row' => $objRow,
        ]);
    }

    /**
     * @Route("/instagram777/services/parse", name="parse_service_instagram777")
     */
    public function inst777parse(ServiceRepository $serviceRepository, Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager):Response
    {
        exit;
        $return = $this->sendCurl($this->UrlFullServicesInstagram777.$this->KeyInstagram777);
        $resp = json_decode($return);

        if(!empty($resp->error)) {
            return new JsonResponse(['error' => 1, 'message' => "Слишком часто нельзя делать запросы"]);
        }
        foreach ($resp as $arrService) {
            $objCategory = $categoryRepository->findOneBy(['API_ID' => $arrService->category_id]);

            if (!is_object($objCategory)) {
                $objCategory = new Category();
                $objCategory->setName($arrService->category_name);
                $objCategory->setDescription($arrService->category_name);
                $objCategory->setAPIID($arrService->category_id);
                $objCategory->setStatus(Category::STATUS_ACTIVE);
                $objCategory->setCreateDate(new \DateTime());
                $entityManager->persist($objCategory);
            }
//            $objRow = $serviceRepository->findOneBy(['Broker' => Service::BROKER_INSTAGRAM777, 'API_ID' => $arrService->ID]);
            $objRow = new Service();
            $objRow->setCategory($objCategory);
            $objRow->setAPIID($arrService->ID);
            $objRow->setName($arrService->name);
            $objRow->setDescription($arrService->description);
            $objRow->setBroker(Service::BROKER_INSTAGRAM777);
            $objRow->setPrice($arrService->cost + $arrService->cost*$this->markup);
            $objRow->setResellerPrice($objRow->getPrice()); // пока не ресселер пусть покупает за ту же цену
            $objRow->setMinQuantity($arrService->min);
            $objRow->setMaxQuantity($arrService->max);
            $objRow->setCreatedDate(new \DateTime());
            $objRow->setOrderAPI($this->CHECKSTATUS_REQUEST777);
            $URL = str_replace('[SERVICE_ID]', $objRow->getAPIID(), $this->API_REQUEST777);
            $objRow->setAPI($URL);

            $objRow->setStatus(Service::STATUS_ACTIVE);
            if ($arrService->active == 'No') {
                $objRow->setStatus(Service::STATUS_DISABLE);
            }

            $entityManager->persist($objRow);
            $entityManager->flush();
        }

        return new JsonResponse(['error' => 0]);
    }

    /**
     * @Route("/instagram777/services/update-price", name="updateprice_service_instagram777")
     */
    public function inst777updateprice(ServiceRepository $serviceRepository, Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager):Response
    {
        $return = $this->sendCurl($this->UrlFullServicesInstagram777.$this->KeyInstagram777);
        $resp = json_decode($return);

        if(!empty($resp->error)) {
            return new JsonResponse(['error' => 1, 'message' => "Слишком часто нельзя делать запросы"]);
        }
        foreach ($resp as $arrService) {
            $objRow = $serviceRepository->findOneBy(['API_ID' =>$arrService->ID]);
            if (!is_object($objRow)) {
                continue;
            }
            $objRow->setAPIID($arrService->ID);
            $objRow->setPrice($arrService->cost + $arrService->cost*$this->markup);
            $objRow->setResellerPrice($objRow->getPrice()); // пока не ресселер пусть покупает за ту же цену
            $objRow->setMinQuantity($arrService->min);
            $objRow->setMaxQuantity($arrService->max);

            $objRow->setStatus(Service::STATUS_ACTIVE);
            if ($arrService->active == 'No') {
                $objRow->setStatus(Service::STATUS_DISABLE);
            }
            $entityManager->flush();
        }

        return new JsonResponse(['error' => 0]);
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

}
