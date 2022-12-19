<?php

namespace App\Entity;

use App\Repository\CrawlLaunchRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CrawlLaunchRepository::class)]
class CrawlLaunch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(name: 'bot_id', type: 'integer')]
    private $botId;

    #[ORM\Column(name: 'start_time', type: 'datetime', nullable: true)]
    private $startTime;

    #[ORM\Column(name: 'end_time', type: 'datetime', nullable: true)]
    private $endTime;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBotId(): ?int
    {
        return $this->botId;
    }

    public function setBotId(int $botId): self
    {
        $this->botId = $botId;

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
