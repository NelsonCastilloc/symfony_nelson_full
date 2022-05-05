<?php

namespace App\Repository;

use App\Entity\HistoryQueues;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoryQueues>
 *
 * @method HistoryQueues|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoryQueues|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoryQueues[]    findAll()
 * @method HistoryQueues[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoryQueuesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryQueues::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(HistoryQueues $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(HistoryQueues $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getTotalQueueTime(){
        $query = " 
            SELECT (SELECT 	((COUNT(1) * 30) -  TIMESTAMPDIFF(MINUTE, hq1.admission_date, NOW()))
                    FROM 	history_queues hq1
                    WHERE 	hq1.queue_number = 1)  as total_time_c1,
                    (SELECT 	((COUNT(1) * 40) -  TIMESTAMPDIFF(MINUTE, hq2.admission_date, NOW()))
                    FROM 	history_queues hq2
                    WHERE 	hq2.queue_number = 2)  as total_time_c2
            FROM 	history_queues hq
            LIMIT 1 ";
        $res = $this->getEntityManager()->getConnection()->executeQuery($query)->fetch();
        return $res;    
    }

    public function getListByQueue() {
        $query = "
            SELECT 	hq.customer_id , hq.customer_name , hq.queue_number 
            FROM 	history_queues hq
            WHERE   hq.attention_start IS NULL ";
        $res = $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAllAssociative();
        return $res;
    }

    public function getInCareProcess($queueNumber){
        $query = "
            SELECT 	hq.id , hq.attention_start , queue_number ,
                    TIMESTAMPDIFF(MINUTE, attention_start , NOW()) as elapsed_in_minutes,
                    TIMESTAMPDIFF(SECOND, attention_start , NOW()) as elapsed_in_seconds
            FROM 	history_queues hq
            WHERE 	attention_start IS NOT NULL
            AND 	hq.queue_number = '$queueNumber'
            ORDER BY queue_number ASC ";
            $res = $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAllAssociative();
            return $res;
    }

    public function getNextToServed($queueNumber){
        $query = " 
            SELECT 	hq1.id
            FROM 	history_queues hq1
            WHERE 	hq1.attention_start IS NULL
            AND 	hq1.queue_number = '$queueNumber'
            ORDER 	BY hq1.id ASC
            LIMIT 	1 ";
            $res = $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAllAssociative();
            return $res;
    }

}
