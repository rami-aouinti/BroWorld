<?php

declare(strict_types=1);

namespace App\General\Transport\Rest\Interfaces;

use App\General\Application\Rest\Interfaces\RestResourceInterface;
use App\General\Application\Rest\Interfaces\RestSmallResourceInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Interface ResponseHandlerInterface
 *
 * @package App\General
 */
interface ResponseHandlerInterface
{
    /**
     * Constants for response output formats.
     */
    public const FORMAT_JSON = 'json';
    public const FORMAT_XML = 'xml';

    public function __construct(SerializerInterface $serializer);

    /**
     * Getter for serializer
     */
    public function getSerializer(): SerializerInterface;

    /**
     * Helper method to get serialization context for request.
     *
     * @return array<int|string, mixed>
     */
    public function getSerializeContext(
        Request $request,
        RestResourceInterface|RestSmallResourceInterface|null $restResource = null
    ): array;

    /**
     * Helper method to create response for request.
     *
     * @param array<int|string, bool|array<int, string>>|null $context
     *
     * @throws HttpException
     */
    public function createResponse(
        Request $request,
        mixed $data,
        RestResourceInterface|RestSmallResourceInterface|null $restResource = null,
        ?int $httpStatus = null,
        ?string $format = null,
        ?array $context = null,
    ): Response;

    /**
     * Method to handle form errors.
     *
     * @throws HttpException
     */
    public function handleFormError(FormInterface $form): void;
}
