<?php
// /src/Repository/CrawlSettingsRepository.php
namespace App\Repository;

use App\Entity\CrawlSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CrawlSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method CrawlSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method CrawlSettings[]    findAll()
 * @method CrawlSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrawlSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawlSettings::class);
    }

    // /**
    //  * @return CrawlSettings[] Returns an array of CrawlSettings objects
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
    public function findOneBySomeField($value): ?CrawlSettings
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function countByUserId($userId): int
    {
        return $this->createQueryBuilder('c')
        ->andWhere('c.user_id = :id')
        ->setParameter('id', $userId)
        ->getQuery()
        ->getSingleScalarResult();
    }
}
