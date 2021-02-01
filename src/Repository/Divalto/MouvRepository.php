<?php

namespace App\Repository\Divalto;

use DateTime;
use App\Entity\Divalto\Mouv;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;

/**
 * @method Mouv|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mouv|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mouv[]    findAll()
 * @method Mouv[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MouvRepository extends ServiceEntityRepository
{

    private $connection;
    public function __construct(ManagerRegistry $registry)
    {
        $this->connection = $registry->getManager('divaltoreel'); 
        parent::__construct($registry, Mouv::class);
    }
    /**
     * @return Mouv[]
     */
    public function test($value):array
    {
        $numeroFacture = 19000100;
        //$entityManager = $this->connection;
        $connect = $this->getEntityManager()->getConnection();
        $conn = $this->getEntityManager()->getConnection();
        dd($conn);

        $sql = '
            SELECT * FROM mouv p
            WHERE p.fano > :fano
            ORDER BY p.fano ASC
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['fano' => $value]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();

    }
    
    public function test2($value)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Divalto\Mouv p
            WHERE p.fadt > :val1 AND p.fadt <= :val2
            ORDER BY p.fano ASC'
        )->setParameter('val1', $value[0])
        ->setParameter('val2', $value[1])
        ;

        // returns an array of Product objects
        return $query->getResult();
    }

    /*
    /**
    * @return Mouv[] Returns an array of Mouv objects
    */
    /*
    public function findByFano($facture)
    {
        return $this->createQueryBuilder('Mouv')
            ->andWhere('Mouv.fano > :val')
            #->innerJoin('a.activiteEnseignements', 'acti')
            ->setParameter('val', $facture)
            #->orderBy('a.id', 'ASC')
            #->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    /*
    /**
    * @return Mouv[] Returns an array of Mouv objects
    */
    /*
    // cette requête fonctionne mais ne tient pas compte du between
    public function findByFadt($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.fadt >= :from')
            ->andWhere('a.fadt <= :to')
            ->orderBy('a.tiers', 'DESC')
            ->setParameter('from', $value[0] )
            ->setParameter('to', $value[1] )
            ->getQuery()
        ;
    }
    */
/*
    public function findByRepr0001($value)
    {
        return $this->createQueryBuilder('a')
            ->select('a.fano , a.fadt, a.repr0001')
            ->Where('a.fadt >= :from ')
            ->andWhere('a.fadt <= :to ')
            ->andWhere('a.repr0001 = :vrp ')
            #->orderBy('a.fano', 'DESC')
            ->setParameter('from', $value[0])
            ->setParameter('to', $value[1])
            ->setParameter('vrp', $value[2])
            ->setMaxResults(100)
            ->getQuery()
        ;
    }
*/

/*

    /*
    public function findByRepr0001( $values )
    {
    $entityManager = $this->getEntityManager();
    $query = $entityManager->getConnection()->prepare('SELECT * FROM mouv WHERE  mouv.repr001 = :repr001 AND mouv.fadt BETWEEN :fromDate AND :toDate');
    $query->execute(array('fromDate' => $values[0], 'toDate' => $values[1], 'repr001' => $values[2]));
    $result = $query->fetch();
    return $result;
    }
    */
    
}
