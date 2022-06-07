<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CrawlLogRepository;

/**
 * CrawlLog
 *
 * @ORM\Table(name="crawl_log")
 * @ORM\Entity(repositoryClass=CrawlLogRepository::class)

 */
class CrawlLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="bot_id", type="integer", nullable=true)
     */
    private $botId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="srv_time_stamp", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $srvTimeStamp = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="scan_date", type="date", nullable=true)
     */
    private $scanDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="scan_time_stamp", type="datetime", nullable=true)
     */
    private $scanTimeStamp;

    /**
     * @var string|null
     *
     * @ORM\Column(name="crawler_name", type="string", length=32, nullable=true)
     */
    private $crawlerName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hostname", type="string", length=128, nullable=true)
     */
    private $hostname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ip_address", type="string", length=128, nullable=true)
     */
    private $ipAddress;

    /**
     * @var int|null
     *
     * @ORM\Column(name="level_number", type="integer", nullable=true)
     */
    private $levelNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="level_name", type="string", length=32, nullable=true)
     */
    private $levelName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="message", type="text", length=65535, nullable=true)
     */
    private $message;

    /**
     * @var int|null
     *
     * @ORM\Column(name="launch_id", type="integer", nullable=true)
     */
    private $launchId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBotId(): ?int
    {
        return $this->botId;
    }

    public function setBotId(?int $botId): self
    {
        $this->botId = $botId;

        return $this;
    }

    public function getSrvDate(): ?\DateTimeInterface
    {
        return $this->srvDate;
    }

    public function setSrvDate(?\DateTimeInterface $srvDate): self
    {
        $this->srvDate = $srvDate;

        return $this;
    }

    public function getSrvTimeStamp(): ?\DateTimeInterface
    {
        return $this->srvTimeStamp;
    }

    public function setSrvTimeStamp(?\DateTimeInterface $srvTimeStamp): self
    {
        $this->srvTimeStamp = $srvTimeStamp;

        return $this;
    }

    public function getScanDate(): ?\DateTimeInterface
    {
        return $this->scanDate;
    }

    public function setScanDate(?\DateTimeInterface $scanDate): self
    {
        $this->scanDate = $scanDate;

        return $this;
    }

    public function getScanTimeStamp(): ?\DateTimeInterface
    {
        return $this->scanTimeStamp;
    }

    public function setScanTimeStamp(?\DateTimeInterface $scanTimeStamp): self
    {
        $this->scanTimeStamp = $scanTimeStamp;

        return $this;
    }

    public function getCrawlerName(): ?string
    {
        return $this->crawlerName;
    }

    public function setCrawlerName(?string $crawlerName): self
    {
        $this->crawlerName = $crawlerName;

        return $this;
    }

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function setHostname(?string $hostname): self
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getLevelNumber(): ?int
    {
        return $this->levelNumber;
    }

    public function setLevelNumber(?int $levelNumber): self
    {
        $this->levelNumber = $levelNumber;

        return $this;
    }

    public function getLevelName(): ?string
    {
        return $this->levelName;
    }

    public function setLevelName(?string $levelName): self
    {
        $this->levelName = $levelName;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getLaunchId(): ?int
    {
        return $this->launchId;
    }

    public function setLaunchId(?int $launchId): self
    {
        $this->launchId = $launchId;

        return $this;
    }
}
