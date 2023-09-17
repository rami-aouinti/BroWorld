<?php

declare(strict_types=1);

namespace App\Log\Application\Service\Interfaces;

use App\Log\Application\Resource\LogRequestResource;
use App\User\Domain\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface RequestLoggerServiceInterface
 *
 * @package App\Log
 */
interface RequestLoggerServiceInterface
{
    /**
     * Constructor
     *
     * @param array<int, string> $sensitiveProperties
     */
    public function __construct(
        LogRequestResource $logRequestResource,
        LoggerInterface $logger,
        array $sensitiveProperties,
        UserRepository $userRepository
    );

    /**
     * Setter for response object.
     */
    public function setResponse(Response $response): self;

    /**
     * Setter for request object.
     */
    public function setRequest(Request $request): self;

    /**
     * Setter method for current user.
     */
    public function setUserId(int $userId): self;

    /**
     * Setter method for 'main request' info.
     */
    public function setMainRequest(bool $mainRequest): self;

    /**
     * Method to handle current response and log it to database.
     */
    public function handle(): void;
}
