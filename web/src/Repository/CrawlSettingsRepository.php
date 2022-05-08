<?php
// /src/Repository/CrawlSettingsRepository.php
namespace App\Repository;

use App\Entity\CrawlSettings;
use App\Entity\User;
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
}
