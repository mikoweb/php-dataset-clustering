<?php

namespace App\UI\CLI;

use App\Infrastructure\Reader\DatasetReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:solve-task',
)]
class SolveTaskCommand extends Command
{
    public function __construct(
        private readonly DatasetReader $datasetReader,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dataset = $this->datasetReader->read();
        dump($dataset->count());

        $io->success('OK');

        return Command::SUCCESS;
    }
}
