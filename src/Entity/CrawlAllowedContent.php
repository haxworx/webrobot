<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CrawlAllowedContent
 */
#[ORM\Table(name: 'crawl_allowed_content')]
#[ORM\Entity]
class CrawlAllowedContent
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'bot_id', type: 'integer', nullable: true)]
    private $botId;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'content_id', type: 'integer', nullable: true)]
    private $contentId;

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

    public function getContentId(): ?int
    {
        return $this->contentId;
    }

    public function setContentId(?int $contentId): self
    {
        $this->contentId = $contentId;

        return $this;
    }


}
