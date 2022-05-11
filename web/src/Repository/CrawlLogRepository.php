<?php
// /src/Repository/CrawlLogRepository.php
namespace App\Repository;

use App\Entity\CrawlLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;

class CrawlLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawlLog::class);
    }

    public function countByBotId($botId): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.botId)')
            ->andWhere('c.botId = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllByBotId($botId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.botId = :id')
            ->setParameter('id', $botId)
            ->getQuery()
            ->execute();
    }

    public function findUniqueScanDatesByBotId(int $botId): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.scanDate')
            ->where('c.botId = :id')
            ->setParameter('id', $botId)
            ->groupBy('c.scanDate')
            ->orderBy('c.scanDate', 'DESC')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);

    }

    public function findAllByBotIdAndScanDate(int $botId, string $scanDate): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.botId = :id')
            ->setParameter('id', $botId)
            ->andWhere('c.scanDate = :scanDate')
            ->setParameter('scanDate', $scanDate)
            ->getQuery()
            ->getResult();
    }

    public function findAllNew($botId, $scanDate, $lastId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.botId = :botId')
            ->setParameter('botId', $botId)
            ->andWhere('c.scanDate = :scanDate')
            ->setParameter('scanDate', $scanDate)
            ->andWhere('c.id > :lastId')
            ->setParameter('lastId', $lastId)
            ->getQuery()
            ->getResult();
    }
}
