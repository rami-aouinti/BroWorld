<?php

declare(strict_types=1);

namespace App\Quiz\Domain\Entity;

use App\Quiz\Domain\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ORM\Table(name: 'quiz')]
class Quiz extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(type: Types::GUID)]
    protected ?string $uuid;

    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: QuizQuestion::class, cascade: ['remove'])]
    protected Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    #[ORM\PrePersist()]
    public function prePersist(): void
    {
        if (empty($this->uuid)) {
            $this->setUuid(uuid_create());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return Collection<int,QuizQuestion>|QuizQuestion[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(QuizQuestion $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setQuiz($this);
        }

        return $this;
    }

    public function removeQuestion(QuizQuestion $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getQuiz() === $this) {
                $question->setQuiz(null);
            }
        }

        return $this;
    }

    /* End basic 'etters ———————————————————————————————————————————————————— */

    public function getScore(): int
    {
        $score = 0;
        foreach ($this->getQuestions() as $quizQuestion) {
            $answer = $quizQuestion->getAnswer();
            if (!$answer instanceof Answer) {
                throw new \LogicException("Can't get the score of a non completed test.");
            }
            $score += $answer->isCorrect() ? 1 : 0;
        }

        return $score;
    }

    /**
     * For a quick view of the score in EasyAdmin.
     */
    public function getAdminScore(): string
    {
        $notAnswered = 0;
        $score = 0;
        $qestions = $this->getQuestions();
        foreach ($qestions as $quizQuestion) {
            $answer = $quizQuestion->getAnswer();
            if ($answer instanceof Answer) {
                $score += $answer->isCorrect() ? 1 : 0;
            } else {
                ++$notAnswered;
            }
        }

        return $score.'/'.(\count($qestions) - $notAnswered).' ('.\count($qestions).')';
    }

    /**
     * Reset all anwers.
     */
    public function reset(): self
    {
        foreach ($this->getQuestions() as $quizQuestion) {
            $quizQuestion->setAnswer(null);
        }

        return $this;
    }
}
