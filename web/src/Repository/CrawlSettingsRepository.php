<?php
// /src/Repository/CrawlSettingsRepository.php
namespace App\Repository;

use App\Entity\CrawlSettings;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;

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

    public function settingsExists(CrawlSettings $crawlSettings, int $userId): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->andWhere('c.userId = :id')
            ->setParameter('id', $userId)
            ->andWhere('c.scheme = :scheme')
            ->setParameter('scheme', $crawlSettings->getScheme())
            ->andWhere('c.domain = :domain')
            ->setParameter('domain', $crawlSettings->getDomain())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function isSameAddress(CrawlSettings $crawlSettings, int $userId): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->andWhere('c.userId = :id')
            ->setParameter('id', $userId)
            ->andWhere('c.address = :address')
            ->setParameter('address', $crawlSettings->getAddress())
            ->andWhere('c.scheme = :scheme')
            ->setParameter('scheme', $crawlSettings->getScheme())
            ->andWhere('c.domain = :domain')
            ->setParameter('domain', $crawlSettings->getDomain())
            ->andWhere('c.botId = :botId')
            ->SetParameter('botId', $crawlSettings->getBotId())
            ->getQuery()
            ->getOneOrNullResult();
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

    public function findOneByBotId($botId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.botId = :id')
            ->setParameter('id', $botId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllBotIdsByUserId($userId): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.botId')
            ->andWhere('c.userId = :id')
            ->setParameter('id', $userId)
            ->groupBy('c.botId')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
        return $result;
    }

    public function findAllByUserId($userId): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.botId, c.address, c.domain, c.scheme, c.agent, c.startTime, c.endTime')
            ->andWhere('c.userId = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getResult();
    }
}
