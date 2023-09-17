<?php

declare(strict_types=1);

namespace App\Log\Transport\Command\Utils;

use App\General\Transport\Command\Traits\SymfonyStyleTrait;
use App\Log\Domain\Repository\Interfaces\LogLoginRepositoryInterface;
use App\Log\Domain\Repository\Interfaces\LogRequestRepositoryInterface;
use App\Log\Infrastructure\Repository\LogLoginRepository;
use App\Log\Infrastructure\Repository\LogRequestRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Class CleanupLogsCommand
 *
 * @package App\Log
 */
#[AsCommand(
    name: self::NAME,
    description: 'Command to cleanup logs(log_login, log_request) in the database.',
)]
class CleanupLogsCommand extends Command
{
    use SymfonyStyleTrait;

    final public const NAME = 'logs:cleanup';

    /**
     * Constructor
     *
     * @param LogLoginRepository $logLoginRepository
     * @param LogRequestRepository $logRequestRepository
     *
     * @throws LogicException
     */
    public function __construct(
        private readonly LogLoginRepositoryInterface $logLoginRepository,
        private readonly LogRequestRepositoryInterface $logRequestRepository,
    ) {
        parent::__construct();
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     *
     * {@inheritdoc}
     *
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getSymfonyStyle($input, $output);
        $result = $this->cleanUpDbTables();

        if ($result && $input->isInteractive()) {
            $io->success('Logs cleanup processed - have a nice day');
        }

        return 0;
    }

    /**
     * Cleanup db tables
     *
     * @throws Throwable
     */
    private function cleanUpDbTables(): bool
    {
        $this->logLoginRepository->cleanHistory();
        $this->logRequestRepository->cleanHistory();

        return true;
    }
}
