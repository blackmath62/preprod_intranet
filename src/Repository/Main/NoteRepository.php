<?php

namespace App\Repository\Main;

use App\Entity\Main\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Note|null find($id, $lockMode = null, $lockVersion = null)
 * @method Note|null findOneBy(array $criteria, array $orderBy = null)
 * @method Note[]    findAll()
 * @method Note[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    // /**
    //  * @return Note[] Returns an array of Note objects
    //  */
    /*
    public function findByExampleField($value)
    {
    return $this->createQueryBuilder('n')
    ->andWhere('n.exampleField = :val')
    ->setParameter('val', $value)
    ->orderBy('n.id', 'ASC')
    ->setMaxResults(10)
    ->getQuery()
    ->getResult()
    ;
    }
     */

    // Compter le nombre de notes par commande
    public function getCountNoteByCmd(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT Id, SUM(Nbe) AS Nbe
        FROM(
        SELECT note.cmdRobyDelaiAccepteReporte_id AS Id,
        CASE
        WHEN note.cmdRobyDelaiAccepteReporte_id > 0 THEN 1
        END AS Nbe
        FROM note)reponse
        GROUP BY Id
        ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }

    /*
public function findOneBySomeField($value): ?Note
{
return $this->createQueryBuilder('n')
->andWhere('n.exampleField = :val')
->setParameter('val', $value)
->getQuery()
->getOneOrNullResult()
;
}
 */
}
