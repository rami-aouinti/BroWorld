<?php

declare(strict_types=1);

namespace App\Quiz\Domain\Entity;

use App\Quiz\Domain\Repository\LinkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Assert\NotBlank]
    #[Assert\Length(max: BaseEntity::STRING_DEFAULT_LENGTH)]
    private ?string $label;

    #[ORM\Column(type: Types::STRING, length: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Assert\NotBlank]
    #[Assert\Length(max: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Assert\Url]
    private ?string $url;

    #[ManyToOne(targetEntity: Question::class, inversedBy: 'links')]
    #[JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Question $question;

    public function __toString(): string
    {
        return (string) $this->label;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }
}
