<?php

namespace App\Repository;

use App\Entity\Equipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Equipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Equipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Equipe[]    findAll()
 * @method Equipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipe::class);
    }

    /**
     * @param int $idDeletedDivision
     * @return int|mixed|string
     */
    public function setDeletedDivisionToNull(int $idDeletedDivision)
    {
        return $this->createQueryBuilder('e')
            ->update('App\Entity\Equipe', 'e')
            ->set('e.idDivision', 'NULL')
            ->where('e.idDivision = :idDeletedDivision')
            ->setParameter('idDeletedDivision', $idDeletedDivision)
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $nomChampionnat
     * @return array
     */
    public function getEquipesDepartementalesApiFFTT(string $nomChampionnat): array
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->leftJoin('e.idChampionnat', 'c')
            ->where('c.nom = :nomChampionnat')
            ->setParameter('nomChampionnat', $nomChampionnat)
            ->orderBy('e.numero')
            ->getQuery()
            ->getResult();
    }
}
