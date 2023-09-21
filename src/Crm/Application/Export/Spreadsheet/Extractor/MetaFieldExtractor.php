<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Export\Spreadsheet\Extractor;

use App\Crm\Transport\Event\MetaDisplayEventInterface;
use App\Crm\Application\Export\Spreadsheet\ColumnDefinition;
use App\Crm\Domain\Entity\EntityWithMetaFields;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class MetaFieldExtractor implements ExtractorInterface
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    /**
     * @param MetaDisplayEventInterface $value
     * @return ColumnDefinition[]
     * @throws ExtractorException
     */
    public function extract($value): array
    {
        if (!($value instanceof MetaDisplayEventInterface)) {
            throw new ExtractorException('MetaFieldExtractor needs a MetaDisplayEventInterface instance for work');
        }

        $columns = [];

        $this->eventDispatcher->dispatch($value);

        foreach ($value->getFields() as $field) {
            if (!$field->isVisible()) {
                continue;
            }

            $columns[] = new ColumnDefinition(
                $field->getLabel(),
                'string',
                function (EntityWithMetaFields $entityWithMetaFields) use ($field) {
                    $meta = $entityWithMetaFields->getMetaField($field->getName());
                    if (null === $meta) {
                        return null;
                    }

                    return $meta->getValue();
                }
            );
        }

        return $columns;
    }
}
