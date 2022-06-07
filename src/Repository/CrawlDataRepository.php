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

    public function getPaginator(int $launchId, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('c')
            ->Where('c.launchId = :launchId')
            ->setParameter('launchId', $launchId)
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery()
        ;

        return new Paginator($query);
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

    public function findOneById($id)
    {
        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
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
