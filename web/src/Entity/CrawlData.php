<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CrawlDataRepository;

/**
 * CrawlData
 *
 * @ORM\Table(name="crawl_data")
 * @ORM\Entity(repositoryClass=CrawlDataRepository::class)
 */
class CrawlData
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
     * @ORM\Column(name="scan_time_zone", type="string", length=64, nullable=true)
     */
    private $scanTimeZone;

    /**
     * @var string|null
     *
     * @ORM\Column(name="domain", type="string", length=253, nullable=true)
     */
    private $domain;

    /**
     * @var string|null
     *
     * @ORM\Column(name="scheme", type="string", length=32, nullable=true)
     */
    private $scheme;

    /**
     * @var string|null
     *
     * @ORM\Column(name="link_source", type="string", length=4096, nullable=true)
     */
    private $linkSource;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url", type="string", length=4096, nullable=true)
     */
    private $url;

    /**
     * @var int|null
     *
     * @ORM\Column(name="status_code", type="integer", nullable=true)
     */
    private $statusCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="path", type="text", length=65535, nullable=true)
     */
    private $path;

    /**
     * @var string|null
     *
     * @ORM\Column(name="query", type="text", length=65535, nullable=true)
     */
    private $query;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content_type", type="string", length=255, nullable=true)
     */
    private $contentType;

    /**
     * @var string|null
     *
     * @ORM\Column(name="metadata", type="text", length=65535, nullable=true)
     */
    private $metadata;

    /**
     * @var string|null
     *
     * @ORM\Column(name="checksum", type="string", length=32, nullable=true)
     */
    private $checksum;

    /**
     * @var string|null
     *
     * @ORM\Column(name="encoding", type="string", length=32, nullable=true)
     */
    private $encoding;

    /**
     * @var int|null
     *
     * @ORM\Column(name="length", type="integer", nullable=true)
     */
    private $length;

    /**
     * @var string|null
     *
     * @ORM\Column(name="data", type="blob", length=16777215, nullable=true)
     */
    private $data;

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

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;

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

    public function getLinkSource(): ?string
    {
        return $this->linkSource;
    }

    public function setLinkSource(?string $linkSource): self
    {
        $this->linkSource = $linkSource;

        return $this;
    }

    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(?\DateTimeInterface $modified): self
    {
        $this->modified = $modified;

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

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(?int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getMetadata(): ?string
    {
        return $this->metadata;
    }

    public function setMetadata(?string $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    public function setChecksum(?string $checksum): self
    {
        $this->checksum = $checksum;

        return $this;
    }

    public function getEncoding(): ?string
    {
        return $this->encoding;
    }

    public function setEncoding(?string $encoding): self
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }


}
