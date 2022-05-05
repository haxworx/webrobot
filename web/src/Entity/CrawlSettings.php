<?php

namespace App\Entity;

use App\Repository\CrawlSettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Validator as CrawlSettingsAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CrawlSettings
 *
 * @ORM\Table(name="crawl_settings")
 * @ORM\Entity(repositoryClass=CrawlSettingsRepository::class)
 */
class CrawlSettings
{
    /**
     * @var int
     *
     * @ORM\Column(name="bot_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $botId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $userId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="scheme", type="string", length=32, nullable=true)
     */
    private $scheme;

    /**
     * @var string|null
     *
     * @ORM\Column(name="address", type="string", length=260, nullable=true)
     */
    #[Assert\Url]
    private $address;

    /**
     * @var string|null
     *
     * @ORM\Column(name="domain", type="string", length=253, nullable=true)
     */
    private $domain;

    /**
     * @var string|null
     *
     * @ORM\Column(name="agent", type="string", length=255, nullable=true)
     */
    #[CrawlSettingsAssert\IsAgent]
    private $agent;

    /**
     * @var float|null
     *
     * @ORM\Column(name="delay", type="float", precision=10, scale=0, nullable=true)
     */
    #[Assert\Range(
        min: 1,
        max: 10,
        notInRangeMessage: 'Must be between {{ min }} and {{ max }}.',
    )]
    private $delay;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="ignore_query", type="boolean", nullable=true)
     */
    private $ignoreQuery;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="import_sitemaps", type="boolean", nullable=true)
     */
    private $importSitemaps;

    /**
     * @var int|null
     *
     * @ORM\Column(name="retry_max", type="integer", nullable=true)
     */
    #[Assert\Range(
        min: 1,
        max: 10,
        notInRangeMessage: 'Must be between {{ min }} and {{ max }}.',
    )]
    private $retryMax;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="start_time", type="time", nullable=true)
     */
    private $startTime;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end_time", type="datetime", nullable=true)
     */
    private $endTime;

    public function getBotId(): ?int
    {
        return $this->botId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function setScheme(?string $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getAgent(): ?string
    {
        return $this->agent;
    }

    public function setAgent(?string $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getDelay(): ?float
    {
        return $this->delay;
    }

    public function setDelay(?float $delay): self
    {
        $this->delay = $delay;

        return $this;
    }

    public function getIgnoreQuery(): ?bool
    {
        return $this->ignoreQuery;
    }

    public function setIgnoreQuery(?bool $ignoreQuery): self
    {
        $this->ignoreQuery = $ignoreQuery;

        return $this;
    }

    public function getImportSitemaps(): ?bool
    {
        return $this->importSitemaps;
    }

    public function setImportSitemaps(?bool $importSitemaps): self
    {
        $this->importSitemaps = $importSitemaps;

        return $this;
    }

    public function getRetryMax(): ?int
    {
        return $this->retryMax;
    }

    public function setRetryMax(?int $retryMax): self
    {
        $this->retryMax = $retryMax;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }
}
