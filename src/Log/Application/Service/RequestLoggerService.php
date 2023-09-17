<?php

declare(strict_types=1);

namespace App\Log\Application\Service;

use App\Log\Application\Resource\LogRequestResource;
use App\Log\Application\Service\Interfaces\RequestLoggerServiceInterface;
use App\Log\Domain\Entity\LogRequest;
use App\User\Domain\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Class RequestLoggerService
 *
 * @package App\Log
 */
class RequestLoggerService implements RequestLoggerServiceInterface
{
    private ?Response $response = null;
    private ?Request $request = null;
    private ?int $userId = null;
    private bool $mainRequest = false;


    /**
     * Constructor
     *
     * @param array<int, string> $sensitiveProperties
     */
    public function __construct(
        private readonly LogRequestResource $logRequestResource,
        private readonly LoggerInterface $logger,
        private readonly array $sensitiveProperties,
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMainRequest(bool $mainRequest): self
    {
        $this->mainRequest = $mainRequest;

        return $this;
    }

    /**
     * Method to handle current response and log it to database.
     */
    public function handle(): void
    {
        // Just check that we have all that we need
        if (!($this->request instanceof Request) || !($this->response instanceof Response)) {
            return;
        }

        try {
            $this->createRequestLogEntry();
        } catch (Throwable $error) {
            $this->logger->error($error->getMessage());
        }
    }

    /**
     * Store request log to database.
     *
     * @throws Throwable
     */
    private function createRequestLogEntry(): void
    {
        /**
         * We want to clear possible existing managements entities before we
         * flush this new `LogRequest` entity to database. This is to prevent
         * not wanted entity state changes to be flushed.
         */
        $this->logRequestResource->getRepository()->getEntityManager()->clear();

        $user = null;

        if ($this->userId !== null) {
            $user = $this->userRepository->find($this->userId);
        }

        // Create new request log entity
        $entity = new LogRequest(
            $this->sensitiveProperties,
            $this->request,
            $this->response,
            $user,
            $this->mainRequest
        );

        $this->logRequestResource->save($entity, true, true);
    }
}
