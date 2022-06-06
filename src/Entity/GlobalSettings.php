<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\GlobalSettingsRepository;

/**
 * GlobalSettings
 *
 * @ORM\Table(name="global_settings")
 * @ORM\Entity(repositoryClass=GlobalSettingsRepository::class)
 */
class GlobalSettings
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
     * @var \DateTime|null
     *
     * @ORM\Column(name="time_stamp", type="datetime", nullable=true)
     */
    private $timeStamp;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="in_use", type="boolean", nullable=true)
     */
    private $inUse;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_crawlers", type="integer", nullable=true)
     */
    private $maxCrawlers;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="debug", type="boolean", nullable=true)
     */
    private $debug;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mqtt_host", type="string", length=128, nullable=true)
     */
    private $mqttHost;

    /**
     * @var int|null
     *
     * @ORM\Column(name="mqtt_port", type="integer", nullable=true)
     */
    private $mqttPort;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mqtt_topic", type="string", length=8192, nullable=true)
     */
    private $mqttTopic;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimeStamp(): ?\DateTimeInterface
    {
        return $this->timeStamp;
    }

    public function setTimeStamp(?\DateTimeInterface $timeStamp): self
    {
        $this->timeStamp = $timeStamp;

        return $this;
    }

    public function getInUse(): ?bool
    {
        return $this->inUse;
    }

    public function setInUse(?bool $inUse): self
    {
        $this->inUse = $inUse;

        return $this;
    }

    public function getMaxCrawlers(): ?int
    {
        return $this->maxCrawlers;
    }

    public function setMaxCrawlers(?int $maxCrawlers): self
    {
        $this->maxCrawlers = $maxCrawlers;

        return $this;
    }

    public function getDebug(): ?bool
    {
        return $this->debug;
    }

    public function setDebug(?bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function getMqttHost(): ?string
    {
        return $this->mqttHost;
    }

    public function setMqttHost(?string $mqttHost): self
    {
        $this->mqttHost = $mqttHost;

        return $this;
    }

    public function getMqttPort(): ?int
    {
        return $this->mqttPort;
    }

    public function setMqttPort(?int $mqttPort): self
    {
        $this->mqttPort = $mqttPort;

        return $this;
    }

    public function getMqttTopic(): ?string
    {
        return $this->mqttTopic;
    }

    public function setMqttTopic(?string $mqttTopic): self
    {
        $this->mqttTopic = $mqttTopic;

        return $this;
    }


}
