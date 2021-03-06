<?php

namespace App\Repository;

use App\Entity\EquipeParis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EquipeParis|null find($id, $lockMode = null, $lockVersion = null)
 * @method EquipeParis|null findOneBy(array $criteria, array $orderBy = null)
 * @method EquipeParis[]    findAll()
 * @method EquipeParis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipeParisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipeParis::class);
    }

    /**
     * @return int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getNbEquipesParis()
    {
        return $this->createQueryBuilder('ep')
            ->select('count(ep.idEquipe)')
            ->where('ep.idDivision IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $fonction
     * @return int|mixed|string
     */
    public function getIdEquipesBrulees(string $fonction)
    {
        return array_column($this->createQueryBuilder('ep')
            ->select('ep.idEquipe')
            ->where('ep.idDivision IS NOT NULL')
            ->andWhere('ep.idEquipe <> (SELECT ' . $fonction . '(e.idEquipe) from App\Entity\EquipeParis e WHERE e.idDivision IS NOT NULL)')
            ->getQuery()
            ->getResult(), 'idEquipe');
    }

    /**
     * Equipes sans affiliation à une division
     * @return int|mixed|string
     */
    public function getEquipesSansDivision()
    {
        return array_column($this->createQueryBuilder('ep')
            ->select('ep.numero')
            ->where('ep.idDivision IS NULL')
            ->orderBy('ep.numero')
            ->getQuery()
            ->getResult(), 'numero');
    }

    /**
     * @param int $idDeletedDivision
     * @return int|mixed|string
     */
    public function setDeletedDivisionToNull(int $idDeletedDivision)
    {
        return $this->createQueryBuilder('ed')
            ->update('App\Entity\EquipeParis', 'ep')
            ->set('ep.idDivision', 'NULL')
            ->where('ep.idDivision = :idDeletedDivision')
            ->setParameter('idDeletedDivision', $idDeletedDivision)
            ->getQuery()
            ->execute();
    }
}
