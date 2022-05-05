<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\HistoryQueues;

class CustomerSupportController extends AbstractController
{
    
    /**
     * @Route("/customer/support",name="customer_support")
     */
    public function CustomerSupportController(ManagerRegistry $em, Request $request): Response
    {
        $list = $em->getRepository( HistoryQueues::class)->getListByQueue();
        
        return $this->render('customer_support/index.html.twig', [
            'list' => $list,
        ]);
    }


    /**
     * @Route("/customer/support/create", name="customer_support_create")
     */
    public function CustomerSupportCreate(ManagerRegistry $em, Request $request)
    {   

        $c_id = $request->get('customer_id');
        $c_name = $request->get('customer_name');
        $dt = new \DateTime();
        $dt->setTimezone(new \DateTimeZone('-0400'));

        //$em = $em->getManager();
        $updateServed = $this->UpdateServed($em);
        dump($updateServed);die;
        $newCustomer = new HistoryQueues();
        $newCustomer->setCustomerId($c_id);
        $newCustomer->setCustomerName($c_name);
        $newCustomer->setAdmissionDate($dt);

        $totalQtime = $em->getRepository( HistoryQueues::class)->getTotalQueueTime();
        if (!is_array($totalQtime)) {
            echo "Aún no hay clientes en cola";
            $newCustomer->setQueueNumber(1);
        }else{
            if ($totalQtime['total_time_c1'] < $totalQtime['total_time_c2']) {
                echo "Se asigna a la cola 1";
                $newCustomer->setQueueNumber(1);
            }else{
                echo "Se asigna a la cola 2";
                $newCustomer->setQueueNumber(2);
            }
        }
        
        $em = $em->getManager();
        $em->persist($newCustomer);
        $em->flush();

        return $this->redirectToRoute('customer_support');
        
    }

    public function UpdateServed($em){

        $em = $em->getManager();

        for ($i=0; $i < 2; $i++) { 
            if ($i == 0) {
                $queue = 1;
                $timeServe = 1800;
            }else{
                $queue = 2;
                $timeServe = 2400;
            }

            $addNextToServe = false;
            $totalQtime = $em->getRepository( HistoryQueues::class)->getInCareProcess($queue);
            if (count($totalQtime) > 0) {
                if ($totalQtime[0]['elapsed_in_seconds'] > $timeServe) {
                    $totalQtime = $totalQtime[0];
                    $customer = $em->getRepository( HistoryQueues::class)->findOneBy(["id" => $totalQtime['id']]);
                    $em->remove($customer);
                    $em->flush();
                    $addNextToServe = true;
                }
            }else{
                $addNextToServe = true;
            }
            if ($addNextToServe) {
                $nextToServe = $em->getRepository( HistoryQueues::class)->getNextToServed($queue);
                //dump($nextToServe);
                $customer = $em->getRepository( HistoryQueues::class)->findOneBy(["id" => $nextToServe[0]['id']]);
                //dump($customer->getCustomerName());
                $dt = new \DateTime();
                $dt->setTimezone(new \DateTimeZone('-0400'));
                $customer->setAttentionStart($dt);
                //dump($customer);
                $em->persist($customer);
                $em->flush();
            }

        }
        
        return 0;
    }


}
