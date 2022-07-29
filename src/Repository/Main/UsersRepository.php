<?php

namespace App\Repository\Main;


use App\Entity\Main\Users;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    // trouver le mail d'un utilisateur en congés
    public function getFindEmail($id)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT users.email AS email FROM holiday
        INNER JOIN users ON holiday.user_id = users.id
        WHERE holiday.id = ?
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Liste des utilisateurs non fermés
    public function getFindAllUsers()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT users.pseudo, users.email, services.title
        FROM users
        LEFT JOIN services ON users.service_id = services.id
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Liste des utilisateurs avec Ev ou Hp à true
    public function getFindUsersEvHp()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT users.email
        FROM users
        WHERE users.ev = 1 OR users.hp = 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Liste des utilisateurs avec Ev Me ou Hp à true
    public function getFindUsersEvHpMe()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT users.email
        FROM users
        WHERE users.ev = 1 OR users.hp = 1 OR users.me = 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}
