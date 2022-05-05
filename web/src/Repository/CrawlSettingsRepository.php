<?php
// /src/Repository/CrawlSettingsRepository.php
namespace App\Repository;

use App\Entity\CrawlSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CrawlSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method CrawlSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method CrawlSettings[]    findAll()
 * @method CrawlSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method CrawlSettings|bool IsNewOrSame(int $userId, string $scheme, string $domain)
 */
class CrawlSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawlSettings::class);
    }

    public function isNewOrSame($userId, $botId, $scheme, $domain): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->andWhere('c.userId = :id')
            ->setParameter('id', $userId)
            ->andWhere('c.scheme = :scheme')
            ->setParameter('scheme', $scheme)
            ->andWhere('c.domain = :domain')
            ->setParameter('domain', $domain)
            ->andWhere('c.botId != :botId')
            ->SetParameter('botId', $botId)
            ->getQuery()
            ->getOneorNullResult();
    }

    public function countByUserId($userId): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.userId)')
            ->andWhere('c.userId = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
