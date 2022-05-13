<?php
// /src/Repository/CrawlDataRepository.php
namespace App\Repository;

use App\Entity\CrawlData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CrawlDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawlData::class);
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

    public function deleteAllByBotId(int $botId): void
    {
        $this->createQueryBuilder('c')
            ->delete()
            ->where('c.botId = :botId')
            ->setParameter('botId', $botId)
            ->getQuery()
            ->execute();
    }

}
