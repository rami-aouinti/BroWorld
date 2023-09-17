<?php

declare(strict_types=1);

namespace App\Quiz\Domain\Entity;

use App\Quiz\Domain\Repository\QuizQuestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity(repositoryClass: QuizQuestionRepository::class)]
#[ORM\Table(name: 'quiz_quiz_question')]
class QuizQuestion extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id;

    #[ManyToOne(targetEntity: Quiz::class, inversedBy: 'questions')]
    #[JoinColumn(nullable: false)]
    protected ?Quiz $quiz;

    #[ManyToOne(targetEntity: Question::class)]
    #[JoinColumn(nullable: false)]
    protected ?Question $question;

    #[ManyToOne(targetEntity: Answer::class)]
    protected ?Answer $answer;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $rank;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;

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

    public function getAnswer(): ?Answer
    {
        return $this->answer;
    }

    public function setAnswer(?Answer $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }
}
