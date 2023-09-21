<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Service\Invoice\Calculator;

use App\Crm\Application\Service\Invoice\CalculatorInterface;
use App\Crm\Domain\Entity\ExportableItem;

/**
 * A calculator that sums up the invoice item records by user.
 */
final class UserInvoiceCalculator extends AbstractSumInvoiceCalculator implements CalculatorInterface
{
    public function getIdentifiers(ExportableItem $invoiceItem): array
    {
        if (null === $invoiceItem->getUser()?->getId()) {
            throw new \Exception('Cannot handle un-persisted user');
        }

        return [
            $invoiceItem->getUser()->getId()
        ];
    }

    public function getId(): string
    {
        return 'user';
    }
}
