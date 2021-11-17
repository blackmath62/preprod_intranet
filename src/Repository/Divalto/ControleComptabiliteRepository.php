<?php

namespace App\Repository\Divalto;


use App\Entity\Divalto\Mouv;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class ControleComptabiliteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mouv::class);
    }
   
    public function getControleTaxesComptabilite($annee,$mois,$typeTiers):array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT MOUV.REF AS ref, MOUV.DES AS des, MOUV.TVAART AS regime,MOUV.FANO AS facture,MOUV.FADT AS date, MOUV.TIERS AS tiers, ENT.CE4 AS status
        FROM MOUV
        INNER JOIN ENT ON MOUV.DOS = ENT.DOS AND MOUV.TIERS = ENT.TIERS AND ENT.PINO = MOUV.FANO
        RIGHT JOIN T085 ON T085.DOS = 999 AND MOUV.TVAART = T085.TVAART
        WHERE MOUV.DOS = 1 --AND ENT.CE4 <> 8
        AND MOUV.REF IN('PCMTAXEGAZOLE10') AND MOUV.TVAART <> 2
        AND YEAR(MOUV.FADT) IN(?) AND MONTH(MOUV.FADT) IN(?) AND MOUV.TICOD = ?
		GROUP BY MOUV.REF, MOUV.DES,MOUV.TVAART, MOUV.FANO, MOUV.FADT, MOUV.TIERS, ENT.CE4";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$annee,$mois,$typeTiers]);
        return $stmt->fetchAll();
    }
    
    public function getControleRegimeTransport($annee,$mois,$typeTiers):array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT ENT.TIERS AS tiers, MOUV.REF AS reference, MOUV.DES AS designation,ART.FAM_0001 AS famille, MOUV.TVAART AS regimeTva,MOUV.FANO AS facture,MOUV.FADT AS dateFacture
        FROM MOUV 
        INNER JOIN ENT ON MOUV.FANO = ENT.PINO AND MOUV.DOS = ENT.DOS AND MOUV.TIERS = ENT.TIERS
        INNER JOIN ART ON MOUV.REF = ART.REF AND MOUV.DOS = ART.DOS
        WHERE MOUV.DOS = 1 AND YEAR(MOUV.FADT) IN(?) AND MONTH(MOUV.FADT) IN(?) AND ART.FAM_0001 IN('TRANSPOR') AND MOUV.TVAART <> 3 AND ENT.TICOD = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$annee,$mois,$typeTiers]);
        return $stmt->fetchAll();
    }

    public function getControleTrousFactures($annee,$mois,$typeTiers):array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT MOUV.FANO AS fano
        FROM MOUV
        WHERE MOUV.DOS = 1 AND YEAR(MOUV.FADT) IN (?) AND MONTH(MOUV.FADT) IN (?) AND MOUV.TICOD = ? AND MOUV.PICOD = 4
        GROUP BY MOUV.FANO
        ORDER BY MOUV.FANO";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$annee,$mois,$typeTiers]);
        return $stmt->fetchAll();
    }

    public function getSendMailErreurRegimeFournisseur():array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT ENT.ENT_ID AS Identification, ENT.PICOD AS typePiece, ENT.PINO AS numeroPiece,
        ENT.TIERS AS tiers, ENT.TVATIE AS regimePiece, FOU.TVATIE AS regimeTiers, ENT.USERCR AS Utilisateur, MUSER.EMAIL AS Email,
        CASE
        WHEN ENT.PICOD = 2 THEN 'Commande Fournisseur'
        WHEN ENT.PICOD = 3 THEN 'BL Fournisseur'
        WHEN ENT.PICOD = 4 THEN 'Facture Fournisseur'
        END AS LibelleTypePiece,
        CASE
        WHEN ENT.TVATIE = '0' THEN 'Régime TVA France'
        WHEN ENT.TVATIE = '01' THEN 'Régime TVA France Autoliquidation'
        WHEN ENT.TVATIE = '1' THEN 'Régime TVA CEE'
        WHEN ENT.TVATIE = '2' THEN 'Régime TVA Hors UE'
        END AS LibelleRegimePiece,
        CASE
        WHEN FOU.TVATIE = '0' THEN 'Régime TVA France'
        WHEN FOU.TVATIE = '01' THEN 'Régime TVA France Autoliquidation'
        WHEN FOU.TVATIE = '1' THEN 'Régime TVA CEE'
        WHEN FOU.TVATIE = '2' THEN 'Régime TVA Hors UE'
        END AS LibelleRegimeTiers
        FROM ENT
        INNER JOIN FOU ON ENT.DOS = FOU.DOS AND ENT.TIERS = FOU.TIERS
        INNER JOIN MUSER ON MUSER.USERX = ENT.USERCR AND MUSER.DOS = ENT.DOS
        WHERE YEAR(ENT.PIDT) >= 2021 AND MONTH(ENT.PIDT) >= 9 AND ENT.TVATIE <> FOU.TVATIE AND ENT.CE4 = 1 AND ENT.PICOD IN (2,3)
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getSendMailErreurRegimeClient():array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT ENT.ENT_ID AS Identification, ENT.PICOD AS typePiece, ENT.PINO AS numeroPiece, 
        ENT.TIERS AS tiers, ENT.TVATIE AS regimePiece, CLI.TVATIE AS regimeTiers, ENT.USERCR AS Utilisateur, MUSER.EMAIL AS Email,
        CASE
        WHEN ENT.PICOD = 2 THEN 'Commande Client'
        WHEN ENT.PICOD = 3 THEN 'BL Client'
        WHEN ENT.PICOD = 4 THEN 'Facture Client'
        END AS LibelleTypePiece,
        CASE
        WHEN ENT.TVATIE = '0' THEN 'Régime TVA France'
        WHEN ENT.TVATIE = '01' THEN 'Régime TVA France Autoliquidation'
        WHEN ENT.TVATIE = '1' THEN 'Régime TVA CEE'
        WHEN ENT.TVATIE = '2' THEN 'Régime TVA Hors UE'
        END AS LibelleRegimePiece,
        CASE
        WHEN CLI.TVATIE = '0' THEN 'Régime TVA France'
        WHEN CLI.TVATIE = '01' THEN 'Régime TVA France Autoliquidation'
        WHEN CLI.TVATIE = '1' THEN 'Régime TVA CEE'
        WHEN CLI.TVATIE = '2' THEN 'Régime TVA Hors UE'
        END AS LibelleRegimeTiers
        FROM ENT
        INNER JOIN CLI ON ENT.DOS = CLI.DOS AND ENT.TIERS = CLI.TIERS
        INNER JOIN MUSER ON MUSER.USERX = ENT.USERCR AND MUSER.DOS = ENT.DOS
        WHERE YEAR(ENT.PIDT) >= 2021 AND MONTH(ENT.PIDT) >= 9 AND ENT.TVATIE <> CLI.TVATIE AND ENT.CE4 = 1 AND ENT.PICOD IN (2,3)
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}
