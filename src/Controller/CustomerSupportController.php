<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\HistoryQueues;

class CustomerSupportController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    /**
     * @Route("/customer/support",name="customer_support")
     */
    public function CustomerSupportController(Request $request): Response
    {
        $this->UpdateServed();
        $mr = $this->doctrine->getManager();
        $list = $mr->getRepository( HistoryQueues::class)->getListByQueue();
        return $this->render('customer_support/index.html.twig', [
            'list' => $list,
        ]);
    }

    /**
     * @Route("/customer/support/create", name="customer_support_create")
     */
    public function CustomerSupportCreate(Request $request)
    {   
        $this->UpdateServed();
        $mr = $this->doctrine->getManager();
        $c_id = $request->get('customer_id');
        $c_name = $request->get('customer_name');
        if(strlen($c_id)>0 && strlen($c_name)>0){

            $dt = new \DateTime();
            $dt->setTimezone(new \DateTimeZone('-0400'));
            $newCustomer = new HistoryQueues();
            $newCustomer->setCustomerId($c_id);
            $newCustomer->setCustomerName($c_name);
            $newCustomer->setAdmissionDate($dt);
            $totalQtime = $mr->getRepository( HistoryQueues::class)->getTotalQueueTime();
            if (!is_array($totalQtime)) {
                $newCustomer->setQueueNumber(1);
            }else{
                if ($totalQtime['total_time_c1'] < $totalQtime['total_time_c2']) {
                    $newCustomer->setQueueNumber(1);
                }else{
                    $newCustomer->setQueueNumber(2);
                }
            }
            $mr->persist($newCustomer);
            $mr->flush();
        }
        return $this->redirectToRoute('customer_support');
    }

    /**
     * @Route("/customer/support/update-served", name="customer_support_update_served")
     */
    public function UpdateServed()
    {
        $mr = $this->doctrine->getManager();
        $refreshView = false;
        for ($i=0; $i < 2; $i++) { 
            if ($i == 0) {
                $queue = 1;
                $timeServe = 120;
            }else{
                $queue = 2;
                $timeServe = 180;
            }
            $addNextToServe = false;
            $inCareProcess = $mr->getRepository( HistoryQueues::class)->getInCareProcess($queue);
            if (count($inCareProcess) > 0) {
                if ($inCareProcess[0]['elapsed_in_seconds'] > $timeServe) {
                    $inCareProcess = $inCareProcess[0];
                    $customer = $mr->getRepository( HistoryQueues::class)->findOneBy(["id" => $inCareProcess['id']]);
                    $mr->remove($customer);
                    $mr->flush();
                    $addNextToServe = true;
                }
            }else{
                $addNextToServe = true;
            }
            if ($addNextToServe) {
                $nextToServe = $mr->getRepository( HistoryQueues::class)->getNextToServed($queue);
                if (count($nextToServe) > 0) {
                    $customer = $mr->getRepository( HistoryQueues::class)->findOneBy(["id" => $nextToServe[0]['id']]);
                    $dt = new \DateTime();
                    $dt->setTimezone(new \DateTimeZone('-0400'));
                    $customer->setAttentionStart($dt);
                    $mr->flush();
                    $refreshView = true;
                }
            }
        }
        $response = new JsonResponse();
        $response->setData([
            "status" => $refreshView,
        ]);
        
        return $response;
    }


}
