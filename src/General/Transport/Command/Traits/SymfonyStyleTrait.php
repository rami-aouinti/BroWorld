<?php

declare(strict_types=1);

namespace App\General\Transport\Command\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Trait SymfonyStyleTrait
 *
 * @package App\General
 */
trait SymfonyStyleTrait
{
    /**
     * Method to get SymfonyStyle object for console commands.
     */
    protected function getSymfonyStyle(
        InputInterface $input,
        OutputInterface $output,
        ?bool $clearScreen = null,
    ): SymfonyStyle {
        $clearScreen ??= true;
        $io = new SymfonyStyle($input, $output);

        if ($clearScreen) {
            $io->write("\033\143");
        }

        return $io;
    }
}
