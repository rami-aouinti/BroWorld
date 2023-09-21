<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\Announce\Domain\Entity\Property;
use App\Crm\Domain\Entity\ColorTrait;
use App\Crm\Domain\Entity\Team;
use App\Crm\Domain\Entity\TeamMember;
use App\Crm\Domain\Entity\UserPreference;
use App\User\Domain\Entity\Traits\EntityIdTrait;
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
use App\User\Domain\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use KevinPapst\TablerBundle\Model\UserInterface as ThemeUserInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use JMS\Serializer\Annotation as Serializer;
use App\Crm\Application\Export\Annotation as Exporter;
use App\Crm\Application\Utils\StringHelper;
use App\Crm\Transport\Validator\Constraints as Constraints;
use DateTime;
use Exception;
use OpenApi\Attributes as OA;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\User\Domain\Repository\UserRepository')]
#[ORM\UniqueConstraint(columns: ['username'])]
#[ORM\UniqueConstraint(columns: ['email'])]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[UniqueEntity('username')]
#[UniqueEntity('email')]
#[Serializer\ExclusionPolicy('all')]
#[Exporter\Order(['id', 'username', 'alias', 'title', 'email', 'last_login', 'language', 'timezone', 'active', 'registeredAt', 'roles', 'teams', 'color', 'accountNumber'])]
#[Exporter\Expose(name: 'email', label: 'email', exp: 'object.getEmail()')]
#[Exporter\Expose(name: 'username', label: 'username', exp: 'object.getUserIdentifier()')]
#[Exporter\Expose(name: 'timezone', label: 'timezone', exp: 'object.getTimezone()')]
#[Exporter\Expose(name: 'language', label: 'language', exp: 'object.getLanguage()')]
#[Exporter\Expose(name: 'last_login', label: 'lastLogin', type: 'datetime', exp: 'object.getLastLogin()')]
#[Exporter\Expose(name: 'roles', label: 'roles', type: 'array', exp: 'object.getRoles()')]
#[Exporter\Expose(name: 'active', label: 'active', type: 'boolean', exp: 'object.isEnabled()')]
#[Constraints\User(groups: ['UserCreate', 'Registration', 'Default', 'Profile'])]
#[Vich\Uploadable]
class User implements UserInterface, EquatableInterface, ThemeUserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    use EntityIdTrait;
    use ColorTrait;

    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_TEAMLEAD = 'ROLE_TEAMLEAD';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const DEFAULT_ROLE = self::ROLE_USER;
    public const DEFAULT_LANGUAGE = 'en';
    public const DEFAULT_FIRST_WEEKDAY = 'monday';
    public const AUTH_INTERNAL = 'kimai';
    public const AUTH_LDAP = 'ldap';
    public const AUTH_SAML = 'saml';
    public const WIZARDS = ['intro', 'profile'];
    final public const PASSWORD_MIN_LENGTH = 8;
    final public const RETRY_TTL = 3600;
    final public const TOKEN_TTL = 43200;

    #[ORM\Column(name: 'alias', type: 'string', length: 60, nullable: true)]
    #[Assert\Length(max: 60)]
    #[Serializer\Expose]
    #[Serializer\Groups(['Default'])]
    #[Exporter\Expose(label: 'alias')]
    private ?string $alias = null;
    /**
     * Registration date for the user
     */
    #[ORM\Column(name: 'registration_date', type: 'datetime', nullable: true)]
    #[Exporter\Expose(label: 'profile.registration_date', type: 'datetime')]
    private ?\DateTime $registeredAt = null;

    #[ORM\Column(name: 'title', type: 'string', length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Serializer\Expose]
    #[Serializer\Groups(['Default'])]
    #[Exporter\Expose(label: 'title')]
    private ?string $title = null;
    /**
     * URL to the user avatar, will be auto-generated if empty
     */
    #[ORM\Column(name: 'avatar', type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255, groups: ['Profile'])]
    #[Serializer\Expose]
    #[Serializer\Groups(['User_Entity'])]
    private ?string $avatar = null;

    /**
     * API token (password) for this user
     */
    #[ORM\Column(name: 'api_token', type: 'string', length: 255, nullable: true)]
    private ?string $apiToken = null;
    /**
     * @internal to be set via form, must not be persisted
     */
    #[Assert\NotBlank(groups: ['ApiTokenUpdate'])]
    #[Assert\Length(min: 8, max: 60, groups: ['ApiTokenUpdate'])]
    private ?string $plainApiToken = null;
    /**
     * User preferences
     *
     * List of preferences for this user, required ones have dedicated fields/methods
     *
     * This Collection can be null for one edge case ONLY:
     * if a currently logged-in user will be deleted and then refreshed from the session from one of the UserProvider
     * e.g. see LdapUserProvider::refreshUser() it might crash if $user->getPreferenceValue() is called
     *
     * @var Collection<UserPreference>|null
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserPreference::class, cascade: ['persist'])]
    private ?Collection $preferences = null;

    /**
     * List of all team memberships.
     *
     * @var Collection<TeamMember>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: TeamMember::class, cascade: ['persist'], fetch: 'LAZY', orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Serializer\Expose]
    #[Serializer\Groups(['User_Entity'])]
    #[OA\Property(type: 'array', items: new OA\Items(ref: '#/components/schemas/TeamMembership'))]
    private Collection $memberships;
    /**
     * The type of authentication used by the user (e.g. "kimai", "ldap", "saml")
     *
     * @internal for internal usage only
     */
    #[ORM\Column(name: 'auth', type: 'string', length: 20, nullable: true)]
    #[Assert\Length(max: 20)]
    private ?string $auth = self::AUTH_INTERNAL;
    /**
     * This flag will be initialized in UserEnvironmentSubscriber.
     *
     * @internal has no database mapping as the value is calculated from a permission
     */
    private ?bool $isAllowedToSeeAllData = null;
    #[ORM\Column(name: 'username', type: 'string', length: 180, nullable: false)]
    #[Assert\NotBlank(groups: ['Registration', 'UserCreate', 'Profile'])]
    #[Assert\Regex(pattern: '/\//', match: false, groups: ['Registration', 'UserCreate', 'Profile'])]
    #[Assert\Length(min: 2, max: 64, groups: ['Registration', 'UserCreate', 'Profile'])]
    #[Serializer\Expose]
    #[Serializer\Groups(['Default'])]
    private ?string $username = null;
    #[ORM\Column(name: 'email', type: 'string', length: 180, nullable: false)]
    #[Assert\NotBlank(groups: ['Registration', 'UserCreate', 'Profile'])]
    #[Assert\Length(min: 2, max: 180)]
    #[Assert\Email(mode: 'html5', groups: ['Registration', 'UserCreate', 'Profile'])]
    private ?string $email = null;
    #[ORM\Column(name: 'account', type: 'string', length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    #[Serializer\Expose]
    #[Serializer\Groups(['Default'])]
    #[Exporter\Expose(label: 'account_number')]
    private ?string $accountNumber = null;
    #[ORM\Column(name: 'enabled', type: 'boolean', nullable: false)]
    #[Serializer\Expose]
    #[Serializer\Groups(['Default'])]
    private bool $enabled = false;
    /**
     * Encrypted password. Must be persisted.
     */
    #[ORM\Column(name: 'password', type: 'string', nullable: false)]
    private ?string $password = null;
    /**
     * Plain password. Used for model validation, not persisted.
     */
    #[Assert\NotBlank(groups: ['Registration', 'PasswordUpdate', 'UserCreate'])]
    #[Assert\Length(min: 8, max: 60, groups: ['Registration', 'PasswordUpdate', 'UserCreate', 'ResetPassword', 'ChangePassword'])]
    private ?string $plainPassword = null;
    #[ORM\Column(name: 'last_login', type: 'datetime', nullable: true)]
    private ?DateTime $lastLogin = null;
    /**
     * Random string sent to the user email address in order to verify it.
     */
    #[ORM\Column(name: 'confirmation_token', type: 'string', length: 180, unique: true, nullable: true)]
    #[Assert\Length(max: 180)]
    private ?string $confirmationToken = null;
    #[ORM\Column(name: 'password_requested_at', type: 'datetime', nullable: true)]
    private ?\DateTime $passwordRequestedAt = null;
    /**
     * List of all role names
     */
    #[ORM\Column(name: 'roles', type: 'array', nullable: false)]
    #[Serializer\Expose]
    #[Serializer\Groups(['User_Entity'])]
    #[Serializer\Type('array<string>')]
    #[Constraints\Role(groups: ['RolesUpdate'])]
    private array $roles = [];
    /**
     * If not empty two-factor authentication is enabled.
     */
    #[ORM\Column(name: 'totp_secret', type: 'string', nullable: true)]
    private ?string $totpSecret = null;
    #[ORM\Column(name: 'totp_enabled', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $totpEnabled = false;
    #[ORM\Column(name: 'system_account', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $systemAccount = false;
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Serializer\Expose]
    #[Serializer\Groups(['User_Entity'])]
    #[OA\Property(ref: '#/components/schemas/User')]
    private ?User $supervisor = null;

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
        $this->registeredAt = new DateTime();
        $this->preferences = new ArrayCollection();
        $this->memberships = new ArrayCollection();
        $this->education = new ArrayCollection();
        $this->experiences = new ArrayCollection();
        $this->hobbies = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->skills = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegisteredAt(): ?DateTime
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(DateTime $registeredAt): User
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    public function setAlias(?string $alias): User
    {
        $this->alias = StringHelper::ensureMaxLength($alias, 60);

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): User
    {
        $this->title = StringHelper::ensureMaxLength($title, 50);

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): User
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): User
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getPlainApiToken(): ?string
    {
        return $this->plainApiToken;
    }

    public function setPlainApiToken(?string $plainApiToken): User
    {
        $this->plainApiToken = $plainApiToken;

        return $this;
    }

    /**
     * Read-only list of of all visible user preferences.
     *
     * @internal only for API usage
     * @return UserPreference[]
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('preferences')]
    #[Serializer\Groups(['User_Entity'])]
    #[OA\Property(type: 'array', items: new OA\Items(ref: '#/components/schemas/UserPreference'))]
    public function getVisiblePreferences(): array
    {
        // hide all internal preferences, which are either available in other fields
        // or which are only used within the Kimai UI
        $skip = [
            UserPreference::TIMEZONE,
            UserPreference::LOCALE,
            UserPreference::SKIN,
            'calendar_initial_view',
            'login_initial_view',
            'update_browser_title',
            'daily_stats',
            'export_decimal',
        ];

        $all = [];
        foreach ($this->preferences as $preference) {
            if ($preference->isEnabled() && !\in_array($preference->getName(), $skip)) {
                $all[] = $preference;
            }
        }

        return $all;
    }

    /**
     * @return Collection<UserPreference>
     */
    public function getPreferences(): Collection
    {
        return $this->preferences;
    }

    /**
     * @param iterable<UserPreference> $preferences
     * @return User
     */
    public function setPreferences(iterable $preferences): User
    {
        $this->preferences = new ArrayCollection();

        foreach ($preferences as $preference) {
            $this->addPreference($preference);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param bool|int|string|float|null $value
     */
    public function setPreferenceValue(string $name, $value = null): void
    {
        $pref = $this->getPreference($name);

        if (null === $pref) {
            $pref = new UserPreference($name);
            $this->addPreference($pref);
        }

        $pref->setValue($value);
    }

    public function getPreference(string $name): ?UserPreference
    {
        if ($this->preferences === null) {
            return null;
        }

        foreach ($this->preferences as $preference) {
            if ($preference->matches($name)) {
                return $preference;
            }
        }

        return null;
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('language')]
    #[Serializer\Groups(['User_Entity'])]
    #[OA\Property(type: 'string')]
    public function getLocale(): string
    {
        return $this->getPreferenceValue(UserPreference::LOCALE, User::DEFAULT_LANGUAGE, false);
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('timezone')]
    #[Serializer\Groups(['User_Entity'])]
    #[OA\Property(type: 'string')]
    public function getTimezone(): string
    {
        return $this->getPreferenceValue(UserPreference::TIMEZONE, date_default_timezone_get(), false);
    }

    public function getLanguage(): string
    {
        return $this->getLocale();
    }

    public function setLanguage(?string $language): void
    {
        if ($language === null) {
            $language = User::DEFAULT_LANGUAGE;
        }
        $this->setPreferenceValue(UserPreference::LOCALE, $language);
    }

    public function isFirstDayOfWeekSunday(): bool
    {
        return $this->getFirstDayOfWeek() === 'sunday';
    }

    public function getFirstDayOfWeek(): string
    {
        return $this->getPreferenceValue(UserPreference::FIRST_WEEKDAY, User::DEFAULT_FIRST_WEEKDAY, false);
    }

    public function isExportDecimal(): bool
    {
        return (bool) $this->getPreferenceValue('export_decimal', false, false);
    }

    public function getSkin(): string
    {
        return (string) $this->getPreferenceValue(UserPreference::SKIN, 'default', false);
    }

    public function setTimezone(?string $timezone): void
    {
        if ($timezone === null) {
            $timezone = date_default_timezone_get();
        }
        $this->setPreferenceValue(UserPreference::TIMEZONE, $timezone);
    }

    /**
     * @param string $name
     * @param bool|int|float|string|null $default
     * @param bool $allowNull
     * @return bool|int|float|string|null
     */
    public function getPreferenceValue(string $name, mixed $default = null, bool $allowNull = true): bool|int|float|string|null
    {
        $preference = $this->getPreference($name);
        if (null === $preference) {
            return $default;
        }

        $value = $preference->getValue();

        return $allowNull ? $value : ($value ?? $default);
    }

    /**
     * @param UserPreference $preference
     * @return User
     */
    public function addPreference(UserPreference $preference): User
    {
        if (null === $this->preferences) {
            $this->preferences = new ArrayCollection();
        }

        $this->preferences->add($preference);
        $preference->setUser($this);

        return $this;
    }

    public function addMembership(TeamMember $member): void
    {
        if ($this->memberships->contains($member)) {
            return;
        }

        if ($member->getUser() === null) {
            $member->setUser($this);
        }

        if ($member->getUser() !== $this) {
            throw new \InvalidArgumentException('Cannot set foreign user membership');
        }

        // when using the API an invalid Team ID triggers the validation too late
        if (($team = $member->getTeam()) === null) {
            return;
        }

        if (null !== $this->findMemberByTeam($team)) {
            return;
        }

        $this->memberships->add($member);
        $team->addMember($member);
    }

    private function findMemberByTeam(Team $team): ?TeamMember
    {
        foreach ($this->memberships as $member) {
            if ($member->getTeam() === $team) {
                return $member;
            }
        }

        return null;
    }

    public function removeMembership(TeamMember $member): void
    {
        if (!$this->memberships->contains($member)) {
            return;
        }

        $this->memberships->removeElement($member);
        if ($member->getTeam() !== null) {
            $member->getTeam()->removeMember($member);
        }
        $member->setUser(null);
        $member->setTeam(null);
    }

    /**
     * @return Collection<TeamMember>
     */
    public function getMemberships(): Collection
    {
        return $this->memberships;
    }

    public function hasMembership(TeamMember $member): bool
    {
        return $this->memberships->contains($member);
    }

    /**
     * Checks if the user is member of any team.
     *
     * @return bool
     */
    public function hasTeamAssignment(): bool
    {
        return !$this->memberships->isEmpty();
    }

    /**
     * Checks is the user is teamlead in any of the assigned teams.
     *
     * @see User::hasTeamleadRole()
     * @return bool
     */
    public function isTeamlead(): bool
    {
        foreach ($this->memberships as $membership) {
            if ($membership->isTeamlead()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the given user is a team member.
     *
     * @param User $user
     * @return bool
     */
    public function hasTeamMember(User $user): bool
    {
        foreach ($this->memberships as $membership) {
            if ($membership->getTeam() !== null && $membership->getTeam()->hasUser($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Use this function to check if the current user can read data from the given user.
     */
    public function canSeeUser(User $user): bool
    {
        if ($user->getId() === $this->getId()) {
            return true;
        }

        if ($this->canSeeAllData()) {
            return true;
        }

        if (!$user->isEnabled()) {
            return false;
        }

        if (!$this->isSystemAccount() && $user->isSystemAccount()) {
            return false;
        }

        if ($this->isTeamleadOfUser($user)) {
            return true;
        }

        return false;
    }

    /**
     * List of all teams, this user is part of
     *
     * @return Team[]
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('teams')]
    #[Serializer\Groups(['User_Entity'])]
    #[OA\Property(type: 'array', items: new OA\Items(ref: '#/components/schemas/Team'))]
    public function getTeams(): iterable
    {
        $teams = [];
        foreach ($this->memberships as $membership) {
            $teams[] = $membership->getTeam();
        }

        return $teams;
    }

    /**
     * Required in the User profile screen to edit his teams.
     *
     * @param Team $team
     */
    public function addTeam(Team $team): void
    {
        foreach ($this->memberships as $membership) {
            if ($membership->getTeam() === $team) {
                return;
            }
        }

        $membership = new TeamMember();
        $membership->setUser($this);
        $membership->setTeam($team);

        $this->addMembership($membership);
    }

    /**
     * Required in the User profile screen to edit his teams.
     *
     * @param Team $team
     */
    public function removeTeam(Team $team): void
    {
        foreach ($this->memberships as $membership) {
            if ($membership->getTeam() === $team) {
                $this->removeMembership($membership);

                return;
            }
        }
    }

    public function isInTeam(Team $team): bool
    {
        foreach ($this->memberships as $membership) {
            if ($membership->getTeam() === $team) {
                return true;
            }
        }

        return false;
    }

    public function isTeamleadOf(Team $team): bool
    {
        if (null !== ($member = $this->findMemberByTeam($team))) {
            return $member->isTeamlead();
        }

        return false;
    }

    public function isTeamleadOfUser(User $user): bool
    {
        foreach ($this->memberships as $membership) {
            if ($membership->isTeamlead() && $membership->getTeam() !== null && $membership->getTeam()->hasUser($user)) {
                return true;
            }
        }

        return false;
    }

    public function canSeeAllData(): bool
    {
        return $this->isSuperAdmin() || true === $this->isAllowedToSeeAllData;
    }

    /**
     * This method should not be called by plugins and returns true on success or false on a failure.
     *
     * @internal immutable property that cannot be set by plugins
     * @param bool $canSeeAllData
     * @return bool
     * @throws Exception
     */
    public function initCanSeeAllData(bool $canSeeAllData): bool
    {
        // prevent manipulation from plugins
        if (null !== $this->isAllowedToSeeAllData) {
            return false;
        }

        $this->isAllowedToSeeAllData = $canSeeAllData;

        return true;
    }

    public function hasTeamleadRole(): bool
    {
        return $this->hasRole(static::ROLE_TEAMLEAD);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(static::ROLE_ADMIN);
    }

    public function getDisplayName(): string
    {
        if (!empty($this->getAlias())) {
            return $this->getAlias();
        }

        return $this->getUserIdentifier();
    }

    public function getAuth(): ?string
    {
        return $this->auth;
    }

    public function setAuth(string $auth): User
    {
        $this->auth = $auth;

        return $this;
    }

    public function isSamlUser(): bool
    {
        return $this->auth === self::AUTH_SAML;
    }

    public function isLdapUser(): bool
    {
        return $this->auth === self::AUTH_LDAP;
    }

    public function isInternalUser(): bool
    {
        return $this->auth === null || $this->auth === self::AUTH_INTERNAL;
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);
        if ($role === static::DEFAULT_ROLE) {
            return;
        }

        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
        $this->plainApiToken = null;
    }

    public function hasUsername(): bool
    {
        return $this->username !== null;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @internal only here to satisfy the theme interface
     */
    public function getIdentifier(): string
    {
        return $this->getUsername();
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function hasEmail(): bool
    {
        return $this->email !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getLastLogin(): ?DateTime
    {
        if ($this->lastLogin !== null) {
            // make sure to use the users own timezone
            $this->lastLogin->setTimezone(new \DateTimeZone($this->getTimezone()));
        }

        return $this->lastLogin;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // we need to make sure to have at least one role
        $roles[] = static::DEFAULT_ROLE;

        return array_values(array_unique($roles));
    }

    public function hasRole($role): bool
    {
        return \in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function setSuperAdmin(bool $isSuper): void
    {
        if (true === $isSuper) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function removeRole($role): User
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setUserIdentifier(string $identifier): void
    {
        $this->setUsername($identifier);
    }

    public function setEmail(?string $email): User
    {
        $this->email = $email;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): User
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function setPassword($password): User
    {
        $this->password = $password;

        return $this;
    }

    public function setPlainPassword($password): User
    {
        $this->plainPassword = $password;

        return $this;
    }

    public function setLastLogin(\DateTime $time = null): User
    {
        $this->lastLogin = $time;

        return $this;
    }

    public function setConfirmationToken($confirmationToken): User
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function setPasswordRequestedAt(\DateTime $date = null): User
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return DateTime|null
     */
    public function getPasswordRequestedAt(): ?DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired(int $seconds): bool
    {
        $date = $this->getPasswordRequestedAt();

        if (!($date instanceof \DateTimeInterface)) {
            return false;
        }

        return $date->getTimestamp() + $seconds > time();
    }

    public function setRoles(array $roles): User
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->username !== $user->getUserIdentifier()) {
            return false;
        }

        return true;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'enabled' => $this->enabled,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }

    public function __unserialize(array $data): void
    {
        if (!\array_key_exists('id', $data)) {
            return;
        }
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->enabled = $data['enabled'];
        $this->email = $data['email'];
        $this->password = $data['password'];
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('initials')]
    #[Serializer\Groups(['Default'])]
    #[OA\Property(type: 'string')]
    public function getInitials(): string
    {
        $length = 2;

        $name = $this->getDisplayName();
        $initial = '';

        if (filter_var($name, FILTER_VALIDATE_EMAIL)) {
            // turn my.email@gmail.com into "My Email"
            $result = mb_strstr($name, '@', true);
            $name = $result === false ? $name : $result;
            $name = str_replace('.', ' ', $name);
        }

        $words = explode(' ', $name);

        // if name contains single word, use first N character
        if (\count($words) === 1) {
            $initial = $words[0];

            if (mb_strlen($name) >= $length) {
                $initial = mb_substr($name, 0, $length, 'UTF-8');
            }
        } else {
            // otherwise, use initial char from each word
            foreach ($words as $word) {
                $initial .= mb_substr($word, 0, 1, 'UTF-8');
            }
            $initial = mb_substr($initial, 0, $length, 'UTF-8');
        }

        $initial = mb_strtoupper($initial);

        return $initial;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): void
    {
        // @CloudRequired because SAML mapping could include a longer value
        $this->accountNumber = StringHelper::ensureMaxLength($accountNumber, 30);
    }

    public function isSystemAccount(): bool
    {
        return $this->systemAccount;
    }

    public function setSystemAccount(bool $isSystemAccount): void
    {
        $this->systemAccount = $isSystemAccount;
    }

    public function getName(): string
    {
        return $this->getDisplayName();
    }

    public function hasSeenWizard(string $wizard): bool
    {
        $wizards = $this->getPreferenceValue('__wizards__');

        if (\is_string($wizards)) {
            $wizards = explode(',', $wizards);

            return \in_array($wizard, $wizards);
        }

        return false;
    }

    public function setWizardAsSeen(string $wizard): void
    {
        $wizards = $this->getPreferenceValue('__wizards__');
        $values = [];

        if (\is_string($wizards)) {
            $values = explode(',', $wizards);
        }

        if (\in_array($wizard, $values)) {
            return;
        }

        $values[] = $wizard;
        $this->setPreferenceValue('__wizards__', implode(',', array_filter($values)));
    }

    // --------------- 2 Factor Authentication ---------------

    public function setTotpSecret(?string $secret): void
    {
        $this->totpSecret = $secret;
    }

    public function hasTotpSecret(): bool
    {
        return $this->totpSecret !== null;
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->totpEnabled;
    }

    public function enableTotpAuthentication(): void
    {
        $this->totpEnabled = true;
    }

    public function disableTotpAuthentication(): void
    {
        $this->totpEnabled = false;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getTotpAuthenticationConfiguration(): TotpConfigurationInterface
    {
        return new TotpConfiguration($this->totpSecret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }

    public function getWorkHoursMonday(): int
    {
        return (int) $this->getPreferenceValue(UserPreference::WORK_HOURS_MONDAY, 0);
    }

    public function getWorkHoursTuesday(): int
    {
        return (int) $this->getPreferenceValue(UserPreference::WORK_HOURS_TUESDAY, 0);
    }

    public function getWorkHoursWednesday(): int
    {
        return (int) $this->getPreferenceValue(UserPreference::WORK_HOURS_WEDNESDAY, 0);
    }

    public function getWorkHoursThursday(): int
    {
        return (int) $this->getPreferenceValue(UserPreference::WORK_HOURS_THURSDAY, 0);
    }

    public function getWorkHoursFriday(): int
    {
        return (int) $this->getPreferenceValue(UserPreference::WORK_HOURS_FRIDAY, 0);
    }

    public function getWorkHoursSaturday(): int
    {
        return (int) $this->getPreferenceValue(UserPreference::WORK_HOURS_SATURDAY, 0);
    }

    public function getWorkHoursSunday(): int
    {
        return (int) $this->getPreferenceValue(UserPreference::WORK_HOURS_SUNDAY, 0);
    }

    public function getWorkStartingDay(): ?\DateTimeInterface
    {
        $date = $this->getPreferenceValue(UserPreference::WORK_STARTING_DAY);

        if ($date === null) {
            return null;
        }

        try {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d h:i:s', $date . ' 00:00:00', new \DateTimeZone($this->getTimezone()));
        } catch (Exception $e) {
        }

        return ($date instanceof \DateTimeInterface) ? $date : null;
    }

    public function setWorkStartingDay(?\DateTimeInterface $date): void
    {
        $this->setPreferenceValue(UserPreference::WORK_STARTING_DAY, $date?->format('Y-m-d'));
    }

    public function getPublicHolidayGroup(): null|string
    {
        $group = $this->getPreferenceValue(UserPreference::PUBLIC_HOLIDAY_GROUP);

        return $group === null ? $group : (string) $group;
    }

    public function getHolidaysPerYear(): int
    {
        return (int) $this->getPreferenceValue(UserPreference::HOLIDAYS_PER_YEAR, 0);
    }

    public function setWorkHoursMonday(int $seconds): void
    {
        $this->setPreferenceValue(UserPreference::WORK_HOURS_MONDAY, $seconds);
    }

    public function setWorkHoursTuesday(int $seconds): void
    {
        $this->setPreferenceValue(UserPreference::WORK_HOURS_TUESDAY, $seconds);
    }

    public function setWorkHoursWednesday(int $seconds): void
    {
        $this->setPreferenceValue(UserPreference::WORK_HOURS_WEDNESDAY, $seconds);
    }

    public function setWorkHoursThursday(int $seconds): void
    {
        $this->setPreferenceValue(UserPreference::WORK_HOURS_THURSDAY, $seconds);
    }

    public function setWorkHoursFriday(int $seconds): void
    {
        $this->setPreferenceValue(UserPreference::WORK_HOURS_FRIDAY, $seconds);
    }

    public function setWorkHoursSaturday(int $seconds): void
    {
        $this->setPreferenceValue(UserPreference::WORK_HOURS_SATURDAY, $seconds);
    }

    public function setWorkHoursSunday(int $seconds): void
    {
        $this->setPreferenceValue(UserPreference::WORK_HOURS_SUNDAY, $seconds);
    }

    public function setPublicHolidayGroup(null|string $group = null): void
    {
        $this->setPreferenceValue(UserPreference::PUBLIC_HOLIDAY_GROUP, $group);
    }

    public function setHolidaysPerYear(?int $holidays): void
    {
        $this->setPreferenceValue(UserPreference::HOLIDAYS_PER_YEAR, $holidays ?? 0);
    }

    public function hasContractSettings(): bool
    {
        return $this->hasWorkHourConfiguration() || $this->getHolidaysPerYear() !== 0;
    }

    public function hasWorkHourConfiguration(): bool
    {
        return $this->getWorkHoursMonday() !== 0 ||
            $this->getWorkHoursTuesday() !== 0 ||
            $this->getWorkHoursWednesday() !== 0 ||
            $this->getWorkHoursThursday() !== 0 ||
            $this->getWorkHoursFriday() !== 0 ||
            $this->getWorkHoursSaturday() !== 0 ||
            $this->getWorkHoursSunday() !== 0;
    }

    public function getWorkHoursForDay(\DateTimeInterface $dateTime): int
    {
        return match ($dateTime->format('N')) {
            '1' => $this->getWorkHoursMonday(),
            '2' => $this->getWorkHoursTuesday(),
            '3' => $this->getWorkHoursWednesday(),
            '4' => $this->getWorkHoursThursday(),
            '5' => $this->getWorkHoursFriday(),
            '6' => $this->getWorkHoursSaturday(),
            '7' => $this->getWorkHoursSunday(),
            default => throw new \Exception('Unknown day: ' . $dateTime->format('Y-m-d'))
        };
    }

    public function isWorkDay(\DateTimeInterface $dateTime): bool
    {
        return $this->getWorkHoursForDay($dateTime) > 0;
    }

    public function hasSupervisor(): bool
    {
        return $this->supervisor !== null;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }

    public function setSupervisor(?User $supervisor): void
    {
        $this->supervisor = $supervisor;
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
