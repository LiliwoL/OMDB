<?php

namespace App\Repository;

use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Vote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vote[]    findAll()
 * @method Vote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function findAverage( $imdbID )
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            "SELECT AVG(v.note) AS moyenne FROM App\Entity\Vote v " .
            "WHERE v.imdbID = :imdbID"
        )->setParameter('imdbID', $imdbID);

        // Pour afficher la requête SQL générée
        //dd ( $query );

        // Renvoie un tableau
        $result = $query->execute();

        // Renvoie juste la première case du tableau que l'on arrondit au chiffre supérieur avec round()
        return round( $result[0]['moyenne'] );
    }

//    /**
//     * @return Vote[] Returns an array of Vote objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Vote
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
