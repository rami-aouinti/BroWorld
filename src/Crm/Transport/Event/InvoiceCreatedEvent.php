<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Transport\Event;

use App\Crm\Application\Service\Invoice\InvoiceModel;
use App\Crm\Domain\Entity\Invoice;
use Symfony\Contracts\EventDispatcher\Event;

final class InvoiceCreatedEvent extends Event
{
    public function __construct(private Invoice $invoice, private InvoiceModel $model)
    {
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function getInvoiceModel(): InvoiceModel
    {
        return $this->model;
    }
}
