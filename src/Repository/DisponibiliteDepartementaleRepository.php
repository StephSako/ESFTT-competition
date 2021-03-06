<?php

namespace App\Repository;

use App\Entity\DisponibiliteDepartementale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DisponibiliteDepartementale|null find($id, $lockMode = null, $lockVersion = null)
 * @method DisponibiliteDepartementale|null findOneBy(array $criteria, array $orderBy = null)
 * @method DisponibiliteDepartementale[]    findAll()
 * @method DisponibiliteDepartementale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DisponibiliteDepartementaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DisponibiliteDepartementale::class);
    }

    /**
     * Liste des personnes ayant déclaré leur disponibilité pour la journée
     * @param $idJournee
     * @return int|mixed|string
     */
    public function findJoueursDeclares($idJournee)
    {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->leftJoin('d.idCompetiteur', 'c')
            ->addSelect('c')
            ->where('d.idJournee = :idJournee')
            ->setParameter('idJournee', $idJournee)
            ->andWhere('c.visitor <> true')
            ->orderBy('d.disponibilite', 'DESC')
            ->addOrderBy('c.nom')
            ->addOrderBy('c.prenom')
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime toutes les disponibilités d'un joueur (cas d'un joueur devenant visiteur)
     * @param int $idCompetiteur
     * @return int|mixed|string
     */
    public function setDeleteDisposVisiteur(int $idCompetiteur)
    {
        return $this->createQueryBuilder('dd')
            ->delete('App\Entity\DisponibiliteDepartementale', 'dd')
            ->where('dd.idCompetiteur = :idCompetiteur')
            ->setParameter('idCompetiteur', $idCompetiteur)
            ->getQuery()
            ->execute();
    }
}
