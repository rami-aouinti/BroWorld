<?php

declare(strict_types=1);

namespace App\Quiz\Domain\Entity;

use App\Quiz\Domain\Repository\AnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
#[ORM\Table(name: 'quiz_answer')]
class Answer extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $code;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $label;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $correct;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $pollResult;

    #[ManyToOne(targetEntity: Question::class, inversedBy: 'answers')]
    #[JoinColumn(nullable: false)]
    private ?Question $question;

    public function __toString(): string
    {
        return (string) $this->code;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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

    public function getCorrect(): ?bool
    {
        return $this->correct;
    }

    /**
     * Alias.
     */
    public function isCorrect(): ?bool
    {
        return $this->getCorrect();
    }

    public function setCorrect(bool $correct): self
    {
        $this->correct = $correct;

        return $this;
    }

    public function getPollResult(): ?int
    {
        return $this->pollResult;
    }

    public function setPollResult(?int $pollResult): self
    {
        $this->pollResult = $pollResult;

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

    /* End basic 'etters ———————————————————————————————————————————————————— */

    public function getLabelWithCode(): string
    {
        return '<b>'.$this->getCode().'</b>: '.$this->getLabel();
    }
}
