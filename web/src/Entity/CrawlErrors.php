<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CrawlErrorsRepository;

/**
 * CrawlErrors
 *
 * @ORM\Table(name="crawl_errors")
 * @ORM\Entity(repositoryClass=CrawlErrorsRepository::class)

 */
class CrawlErrors
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
     * @ORM\Column(name="srv_date", type="date", nullable=true, options={"default"="curdate()"})
     */
    private $srvDate = 'curdate()';

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
     * @ORM\Column(name="scan_time_zone", type="string", length=64, nullable=true)
     */
    private $scanTimeZone;

    /**
     * @var int|null
     *
     * @ORM\Column(name="status_code", type="integer", nullable=true)
     */
    private $statusCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url", type="string", length=4096, nullable=true)
     */
    private $url;

    /**
     * @var string|null
     *
     * @ORM\Column(name="link_source", type="string", length=4096, nullable=true)
     */
    private $linkSource;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

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

    public function getScanTimeZone(): ?string
    {
        return $this->scanTimeZone;
    }

    public function setScanTimeZone(?string $scanTimeZone): self
    {
        $this->scanTimeZone = $scanTimeZone;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(?int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getLinkSource(): ?string
    {
        return $this->linkSource;
    }

    public function setLinkSource(?string $linkSource): self
    {
        $this->linkSource = $linkSource;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }


}
