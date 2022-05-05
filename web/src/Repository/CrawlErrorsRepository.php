<?php
// /src/Repository/CrawlErrorsRepository.php
namespace App\Repository;

use App\Entity\CrawlErrors;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CrawlErrorsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawlErrors::class);
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
}
