<?php

namespace App\Repository;

use App\Entity\CrawlLaunch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CrawlLaunch>
 *
 * @method CrawlLaunch|null find($id, $lockMode = null, $lockVersion = null)
 * @method CrawlLaunch|null findOneBy(array $criteria, array $orderBy = null)
 * @method CrawlLaunch[]    findAll()
 * @method CrawlLaunch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrawlLaunchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawlLaunch::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CrawlLaunch $entity, bool $flush = true): void
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
    public function remove(CrawlLaunch $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return CrawlLaunch[] Returns an array of CrawlLaunch objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CrawlLaunch
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
