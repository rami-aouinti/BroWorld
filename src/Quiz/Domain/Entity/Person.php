<?php

declare(strict_types=1);

namespace App\Quiz\Domain\Entity;

use App\Quiz\Domain\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[UniqueEntity('twitter')]
#[UniqueEntity('pseudo')]
#[ORM\Table(name: 'quiz_author')]
class Person extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: BaseEntity::STRING_DEFAULT_LENGTH, unique: true, nullable: true)]
    #[Assert\Length(max: BaseEntity::STRING_DEFAULT_LENGTH)]
    private ?string $twitter = null;

    #[ORM\Column(type: Types::STRING, length: BaseEntity::STRING_DEFAULT_LENGTH, unique: true, nullable: true)]
    #[Assert\Length(max: BaseEntity::STRING_DEFAULT_LENGTH)]
    private ?string $pseudo = null;

    #[ORM\OneToMany(mappedBy: 'suggestedBy', targetEntity: Question::class, orphanRemoval: true)]
    private Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) ($this->twitter ?: $this->pseudo);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): self
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * @return Collection<int,Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setSuggestedBy($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getSuggestedBy() === $this) {
                $question->setSuggestedBy(null);
            }
        }

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        $person = $context->getValue();
        if (!$person instanceof self) {
            throw new \UnexpectedValueException('Invalid type, a Person object is expected.');
        }

        if ($person->getTwitter() && $person->getPseudo()) {
            $context->buildViolation('You should fill the Twitter or the pseudo, but not both.')
                ->atPath('twitter')
                ->addViolation();
        }
    }
}
