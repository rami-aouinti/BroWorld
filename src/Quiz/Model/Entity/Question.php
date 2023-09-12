<?php

declare(strict_types=1);

namespace App\Quiz\Model\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Quiz\Model\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'show']),
        new GetCollection(normalizationContext: ['groups' => 'show'])
    ]
)]
class Question extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['show'])]
    protected ?int $id = null; // for the unit tests

    #[ORM\Column(type: Types::STRING, length: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Assert\NotBlank]
    #[Assert\Length(max: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Groups(['show'])]
    protected ?string $label;

    #[ORM\Column(type: Types::STRING, length: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Assert\NotBlank]
    #[Assert\Length(max: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Assert\Url]
    #[Groups(['show'])]
    protected ?string $codeImage;

    #[ORM\Column(type: Types::STRING, length: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Assert\NotBlank]
    #[Assert\Length(max: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Groups(['show'])]
    protected ?string $codeImageFile;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(max: BaseEntity::STRING_DEFAULT_LENGTH)]
    protected ?string $answerExplanations;

    #[ORM\Column(type: Types::STRING, length: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Assert\NotBlank]
    #[Assert\Length(max: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Assert\Url]
    protected ?string $liveSnippetUrl;

    #[ORM\Column(type: Types::STRING, length: BaseEntity::STRING_DEFAULT_LENGTH, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: BaseEntity::STRING_DEFAULT_LENGTH)]
    #[Assert\Url]
    protected ?string $twitterPollUrl;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $differencesOutputNotes;

    #[OneToMany(mappedBy: 'question', targetEntity: Answer::class, fetch: 'EAGER', orphanRemoval: true)]
    protected Collection $answers;

    #[OneToMany(mappedBy: 'question', targetEntity: Link::class, fetch: 'EAGER', orphanRemoval: true)]
    protected Collection $links;

    #[ManyToOne(targetEntity: Person::class, fetch: 'EAGER', inversedBy: 'questions')]
    #[JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    protected ?Person $suggestedBy;

    #[OneToOne(targetEntity: Question::class, cascade: ["persist", "remove"], fetch: 'EAGER')]
    protected ?Question $previousQuestion;

    #[OneToOne(targetEntity: Question::class, cascade: ["persist", "remove"], fetch: 'EAGER')]
    protected ?Question $nextQuestion;

    #[ManyToOne(targetEntity: Difficulty::class, inversedBy: 'questions')]
    #[JoinColumn(nullable: false)]
    protected ?Difficulty $difficulty;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->links = new ArrayCollection();
    }

    public function __toString(): string
    {
        return 'Question n°'.$this->id;
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

    public function getCodeImage(): ?string
    {
        return $this->codeImage;
    }

    public function setCodeImage(string $codeImage): self
    {
        $this->codeImage = $codeImage;

        return $this;
    }

    public function getCodeImageFile(): ?string
    {
        return $this->codeImageFile;
    }

    public function setCodeImageFile(string $codeImageFile): self
    {
        $this->codeImageFile = $codeImageFile;

        return $this;
    }

    public function getAnswerExplanations(): ?string
    {
        return $this->answerExplanations;
    }

    public function setAnswerExplanations(string $answerExplanations): self
    {
        $this->answerExplanations = $answerExplanations;

        return $this;
    }

    public function getLiveSnippetUrl(): ?string
    {
        return $this->liveSnippetUrl;
    }

    public function setLiveSnippetUrl(string $liveSnippetUrl): self
    {
        $this->liveSnippetUrl = $liveSnippetUrl;

        return $this;
    }

    public function getTwitterPollUrl(): ?string
    {
        return $this->twitterPollUrl;
    }

    public function setTwitterPollUrl(?string $twitterPollUrl): self
    {
        $this->twitterPollUrl = $twitterPollUrl;

        return $this;
    }

    public function getDifferencesOutputNotes(): ?string
    {
        return $this->differencesOutputNotes;
    }

    public function setDifferencesOutputNotes(?string $differencesOutputNotes): self
    {
        $this->differencesOutputNotes = $differencesOutputNotes;

        return $this;
    }

    /**
     * @return Collection<int,Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int,Link>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(Link $link): self
    {
        if (!$this->links->contains($link)) {
            $this->links[] = $link;
            $link->setQuestion($this);
        }

        return $this;
    }

    public function removeLink(Link $link): self
    {
        if ($this->links->contains($link)) {
            $this->links->removeElement($link);
            // set the owning side to null (unless already changed)
            if ($link->getQuestion() === $this) {
                $link->setQuestion(null);
            }
        }

        return $this;
    }

    public function getSuggestedBy(): ?Person
    {
        return $this->suggestedBy;
    }

    public function setSuggestedBy(?Person $suggestedBy): self
    {
        $this->suggestedBy = $suggestedBy;

        return $this;
    }

    public function getPreviousQuestion(): ?self
    {
        return $this->previousQuestion;
    }

    public function setPreviousQuestion(?self $previousQuestion): self
    {
        $this->previousQuestion = $previousQuestion;

        return $this;
    }

    public function getNextQuestion(): ?self
    {
        return $this->nextQuestion;
    }

    public function setNextQuestion(?self $nextQuestion): self
    {
        $this->nextQuestion = $nextQuestion;

        return $this;
    }

    public function getDifficulty(): ?Difficulty
    {
        return $this->difficulty;
    }

    public function setDifficulty(?Difficulty $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    /* End basic 'etters ———————————————————————————————————————————————————— */

    /**
     * Virtual property getter.
     *
     * @Groups({"show"})
     *
     * @throws \LogicException
     */
    public function getCorrectAnswerCode(): string
    {
        $correctAnswer = null;
        foreach ($this->getAnswers() as $answer) {
            if (null !== $correctAnswer && $answer->isCorrect()) {
                throw new \LogicException('Question has more than a correct answer.');
            }

            if (null === $correctAnswer && $answer->isCorrect()) {
                $correctAnswer = $answer;
            }
        }

        if (null === $correctAnswer) {
            throw new \LogicException("Question doesn't have a correct answer.");
        }

        return (string) $correctAnswer->getCode();
    }
}
