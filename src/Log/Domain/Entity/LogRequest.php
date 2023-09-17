<?php

declare(strict_types=1);

namespace App\Log\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\Log\Domain\Entity\Traits\LogEntityTrait;
use App\Log\Domain\Entity\Traits\LogRequestProcessRequestTrait;
use App\User\Domain\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

use function mb_strlen;

/**
 * Class LogRequest
 *
 * @package App\Log
 */
#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'log_request')]
#[ORM\Index(
    columns: ['user_id'],
    name: 'user_id',
)]
#[ORM\Index(
    columns: ['date'],
    name: 'request_date',
)]
#[ORM\HasLifecycleCallbacks]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class LogRequest implements EntityInterface
{
    use LogEntityTrait;
    use LogRequestProcessRequestTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(
        name: 'status_code',
        type: Types::INTEGER,
    )]
    #[Groups([
        'LogRequest',
        'LogRequest.statusCode',
    ])]
    private int $statusCode = 0;

    #[ORM\Column(
        name: 'response_content_length',
        type: Types::INTEGER,
    )]
    #[Groups([
        'LogRequest',
        'LogRequest.responseContentLength',
    ])]
    private int $responseContentLength = 0;

    #[ORM\Column(
        name: 'is_main_request',
        type: Types::BOOLEAN,
    )]
    #[Groups([
        'LogRequest',
        'LogRequest.isMainRequest',
    ])]
    private bool $mainRequest;

    /**
     * Constructor
     *
     * @param array<int, string> $sensitiveProperties
     *
     * @throws Throwable
     */
    public function __construct(
        private array $sensitiveProperties,
        ?Request $request = null,
        ?Response $response = null,
        #[ORM\ManyToOne(
            targetEntity: User::class,
            inversedBy: 'logsRequest',
        )]
        #[ORM\JoinColumn(
            name: 'user_id',
            onDelete: 'SET NULL',
        )]
        #[Groups([
            'LogRequest.user',
        ])]
        private ?User $user = null,
        ?bool $mainRequest = null
    ) {
        $this->mainRequest = $mainRequest ?? true;

        $this->processTimeAndDate();

        if ($request !== null) {
            $this->processRequestData($request);
            $this->processRequest($request);
        }

        if ($response !== null) {
            $this->processResponse($response);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseContentLength(): int
    {
        return $this->responseContentLength;
    }

    public function isMainRequest(): bool
    {
        return $this->mainRequest;
    }

    /**
     * @return array<int, string>
     */
    public function getSensitiveProperties(): array
    {
        return $this->sensitiveProperties;
    }

    private function processResponse(Response $response): void
    {
        $content = $response->getContent();
        $this->statusCode = $response->getStatusCode();
        $this->responseContentLength = $content === false ? 0 : mb_strlen($content);
    }
}
