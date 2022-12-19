<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContentTypes
 */
#[ORM\Table(name: 'content_types')]
#[ORM\Entity]
class ContentTypes
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'content_id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $contentId;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'content_type', type: 'string', length: 128, nullable: true)]
    private $contentType;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'description', type: 'text', length: 65535, nullable: true)]
    private $description;

    public function getContentId(): ?int
    {
        return $this->contentId;
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
