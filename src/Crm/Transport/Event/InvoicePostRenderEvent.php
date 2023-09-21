<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Event;

use App\Crm\Application\Model\InvoiceDocument;
use App\Crm\Application\Service\Invoice\InvoiceModel;
use App\Crm\Application\Service\Invoice\RendererInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

final class InvoicePostRenderEvent extends Event
{
    public function __construct(private InvoiceModel $model, private InvoiceDocument $document, private RendererInterface $renderer, private Response $response)
    {
    }

    public function getModel(): InvoiceModel
    {
        return $this->model;
    }

    public function getDocument(): InvoiceDocument
    {
        return $this->document;
    }

    public function getRenderer(): RendererInterface
    {
        return $this->renderer;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}