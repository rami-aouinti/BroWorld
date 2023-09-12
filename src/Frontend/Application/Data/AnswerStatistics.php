<?php

declare(strict_types=1);

namespace App\Frontend\Application\Data;

final class AnswerStatistics
{
    /**
     * @var array<string, int>
     */
    public array $answerCodes = [];
    public int $sum = 0;
}
