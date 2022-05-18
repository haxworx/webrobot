<?php
// /src/Repository/CrawlDataRepository.php
namespace App\Repository;

use App\Entity\CrawlData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\AbstractQuery;

class CrawlDataRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 5;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrawlData::class);
    }

    public function getPaginator(int $botId, string $scanDate, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('c')
            ->Where('c.botId = :botId')
            ->setParameter('botId', $botId)
            ->andWhere('c.scanDate = :scanDate')
            ->setParameter('scanDate', $scanDate)
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery()
        ;

        return new Paginator($query);
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
