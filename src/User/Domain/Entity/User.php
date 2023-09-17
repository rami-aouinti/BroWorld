<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\Announce\Domain\Entity\Property;
use App\User\Domain\Entity\Traits\EntityIdTrait;
use App\User\Domain\Entity\Traits\TwoFactorTrait;
use App\Frontend\Domain\Entity\Setting;
use App\Log\Domain\Entity\LogLogin;
use App\Log\Domain\Entity\LogLoginFailure;
use App\Log\Domain\Entity\LogRequest;
use App\Resume\Domain\Entity\Education;
use App\Resume\Domain\Entity\Experience;
use App\Resume\Domain\Entity\Hobby;
use App\Resume\Domain\Entity\Language;
use App\Resume\Domain\Entity\Projects;
use App\Resume\Domain\Entity\Skill;
use App\Task\Domain\Entity\Task;
use App\User\Domain\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use EntityIdTrait;


    final public const ROLE_USER = 'ROLE_USER';
    final public const ROLE_ADMIN = 'ROLE_ADMIN';

    final public const PASSWORD_MIN_LENGTH = 8;

    /**
     * Requests older than this many seconds will be considered expired.
     */
    final public const RETRY_TTL = 3600;
    /**
     * Maximum time that the confirmation token will be valid.
     */
    final public const TOKEN_TTL = 43200;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $lastName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nationality = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $state = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $housnumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthday = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $position = null;

    #[ORM\Column(nullable: true)]
    public ?string $photo;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebookId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hostedDomain = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[Vich\UploadableField(mapping: 'users', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $brochureFilename;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Property::class)]
    private $properties;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private $confirmation_token;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private $password_requested_at;


    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Profile::class, cascade: ['persist', 'remove'])]
    private ?Profile $profile;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private $emailVerifiedAt;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="followers")
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'followers')]
    private Collection $followed;

    /**
     * @var Collection|User[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="followed")
     * @ORM\JoinTable(
     *   name="rw_user_follower",
     *   joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="follower_id", referencedColumnName="id")}
     * )
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'followed')]
    #[ORM\JoinTable(name: 'rw_user_follower')]
    private Collection $followers;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Setting $setting = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Task::class)]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Education::class)]
    private Collection $education;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Experience::class)]
    private Collection $experiences;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Hobby::class)]
    private Collection $hobbies;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Language::class)]
    private Collection $languages;

    #[ORM\ManyToMany(targetEntity: Projects::class, mappedBy: 'user')]
    private Collection $projects;

    #[ORM\ManyToMany(targetEntity: Skill::class, mappedBy: 'user')]
    private Collection $skills;

    #[ORM\OneToMany(
        mappedBy: 'user',
        targetEntity: LogRequest::class,
    )]
    #[Groups([
        'User.logsRequest',
    ])]
    protected Collection | ArrayCollection $logsRequest;

    #[ORM\OneToMany(
        mappedBy: 'user',
        targetEntity: LogLogin::class,
    )]
    #[Groups([
        'User.logsLogin',
    ])]
    protected Collection | ArrayCollection $logsLogin;

    /**
     * @var Collection<int, LogLoginFailure>|ArrayCollection<int, LogLoginFailure>
     */
    #[ORM\OneToMany(
        mappedBy: 'user',
        targetEntity: LogLoginFailure::class,
    )]
    #[Groups([
        'User.logsLoginFailure',
    ])]
    protected Collection | ArrayCollection $logsLoginFailure;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->education = new ArrayCollection();
        $this->experiences = new ArrayCollection();
        $this->hobbies = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->skills = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @return $this
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Getter for user request log collection.
     *
     * @return Collection<int, LogRequest>|ArrayCollection<int, LogRequest>
     */
    public function getLogsRequest(): Collection | ArrayCollection
    {
        return $this->logsRequest;
    }

    /**
     * Getter for user login log collection.
     *
     * @return Collection<int, LogLogin>|ArrayCollection<int, LogLogin>
     */
    public function getLogsLogin(): Collection | ArrayCollection
    {
        return $this->logsLogin;
    }

    /**
     * Getter for user login failure log collection.
     *
     * @return Collection<int, LogLoginFailure>|ArrayCollection<int, LogLoginFailure>
     */
    public function getLogsLoginFailure(): Collection | ArrayCollection
    {
        return $this->logsLoginFailure;
    }

    public function follows(User $user): bool
    {
        return $this->followed->contains($user);
    }

    public function follow(User $user): void
    {
        if ($user->getFollowers()->contains($this)) {
            return;
        }

        $user->getFollowers()->add($this);
    }

    public function unfollow(User $user): void
    {
        if (!$user->getFollowers()->contains($this)) {
            return;
        }

        $user->getFollowers()->removeElement($this);
    }

    /**
     * @return Collection|User[]
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    /**
     * @param Collection|User[] $followers
     */
    public function setFollowers(Collection $followers): void
    {
        $this->followers = $followers;
    }

    /**
     * @return Collection|User[]
     */
    public function getFolloweds(): Collection
    {
        return $this->followed;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     */
    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    /**
     * @param string|null $nationality
     */
    public function setNationality(?string $nationality): void
    {
        $this->nationality = $nationality;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string|null $street
     */
    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return string|null
     */
    public function getHousnumber(): ?string
    {
        return $this->housnumber;
    }

    /**
     * @param string|null $housnumber
     */
    public function setHousnumber(?string $housnumber): void
    {
        $this->housnumber = $housnumber;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    /**
     * @param \DateTimeInterface|null $birthday
     */
    public function setBirthday(?\DateTimeInterface $birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return string|null
     */
    public function getPosition(): ?string
    {
        return $this->position;
    }

    /**
     * @param string|null $position
     */
    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    /**
     * @return string|null
     */
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    /**
     * @param string|null $photo
     */
    public function setPhoto(?string $photo): void
    {
        $this->photo = $photo;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * @param mixed $googleId
     */
    public function setGoogleId($googleId): void
    {
        $this->googleId = $googleId;
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param mixed $avatar
     */
    public function setAvatar($avatar): void
    {
        $this->avatar = $avatar;
    }

    /**
     * @return mixed
     */
    public function getHostedDomain()
    {
        return $this->hostedDomain;
    }

    /**
     * @param mixed $hostedDomain
     */
    public function setHostedDomain($hostedDomain): void
    {
        $this->hostedDomain = $hostedDomain;
    }

    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    public function setFacebookId(?string $facebookId): void
    {
        $this->facebookId = $facebookId;
    }

    public function getSetting(): ?Setting
    {
        return $this->setting;
    }

    public function setSetting(?Setting $setting): static
    {
        // unset the owning side of the relation if necessary
        if ($setting === null && $this->setting !== null) {
            $this->setting->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($setting !== null && $setting->getUser() !== $this) {
            $setting->setUser($this);
        }

        $this->setting = $setting;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setUser($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Education>
     */
    public function getEducation(): Collection
    {
        return $this->education;
    }

    public function addEducation(Education $education): static
    {
        if (!$this->education->contains($education)) {
            $this->education->add($education);
            $education->setUser($this);
        }

        return $this;
    }

    public function removeEducation(Education $education): static
    {
        if ($this->education->removeElement($education)) {
            // set the owning side to null (unless already changed)
            if ($education->getUser() === $this) {
                $education->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Experience>
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(Experience $experience): static
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences->add($experience);
            $experience->setUser($this);
        }

        return $this;
    }

    public function removeExperience(Experience $experience): static
    {
        if ($this->experiences->removeElement($experience)) {
            // set the owning side to null (unless already changed)
            if ($experience->getUser() === $this) {
                $experience->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Hobby>
     */
    public function getHobbies(): Collection
    {
        return $this->hobbies;
    }

    public function addHobby(Hobby $hobby): static
    {
        if (!$this->hobbies->contains($hobby)) {
            $this->hobbies->add($hobby);
            $hobby->setUser($this);
        }

        return $this;
    }

    public function removeHobby(Hobby $hobby): static
    {
        if ($this->hobbies->removeElement($hobby)) {
            // set the owning side to null (unless already changed)
            if ($hobby->getUser() === $this) {
                $hobby->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Language>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): static
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
            $language->setUser($this);
        }

        return $this;
    }

    public function removeLanguage(Language $language): static
    {
        if ($this->languages->removeElement($language)) {
            // set the owning side to null (unless already changed)
            if ($language->getUser() === $this) {
                $language->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Projects>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Projects $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->addUser($this);
        }

        return $this;
    }

    public function removeProject(Projects $project): static
    {
        if ($this->projects->removeElement($project)) {
            $project->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->addUser($this);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        if ($this->skills->removeElement($skill)) {
            $skill->removeUser($this);
        }

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function getBrochureFilename(): string
    {
        return $this->brochureFilename;
    }

    public function setBrochureFilename(string $brochureFilename): self
    {
        $this->brochureFilename = $brochureFilename;

        return $this;
    }

    public function __serialize(): array
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        return [$this->id, $this->email, $this->password];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        // add $this->salt too if you don't use Bcrypt or Argon2i
        [$this->id, $this->email, $this->password] = $data;
    }

    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function addProperty(Property $property): self
    {
        if (!$this->properties->contains($property)) {
            $this->properties[] = $property;
            $property->setAuthor($this);
        }

        return $this;
    }

    public function removeProperty(Property $property): self
    {
        if ($this->properties->contains($property)) {
            $this->properties->removeElement($property);
            // set the owning side to null (unless already changed)
            if ($property->getAuthor() === $this) {
                $property->setAuthor(null);
            }
        }

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmation_token;
    }

    public function setConfirmationToken(?string $confirmation_token): self
    {
        $this->confirmation_token = $confirmation_token;

        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->password_requested_at;
    }

    public function setPasswordRequestedAt(?\DateTimeInterface $password_requested_at): self
    {
        $this->password_requested_at = $password_requested_at;

        return $this;
    }

    /**
     * Checks whether the password reset request has expired.
     */
    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime
            && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): self
    {
        // set the owning side of the relation if necessary
        if ($profile->getUser() !== $this) {
            $profile->setUser($this);
        }

        $this->profile = $profile;

        return $this;
    }

    public function getEmailVerifiedAt(): ?\DateTime
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?\DateTime $dateTime): self
    {
        $this->emailVerifiedAt = $dateTime;

        return $this;
    }
}
