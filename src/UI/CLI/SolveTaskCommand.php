<?php

namespace App\UI\CLI;

use App\Application\Analytics\ClusteringAnalysisDatasetFactory;
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
        private readonly ClusteringAnalysisDatasetFactory $clusteringAnalysisDatasetFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Dataset reading...');
        $dataset = $this->datasetReader->read();

        $io->info('Clustering analysis dataset creation...');
        $clusteringAnalysisDataset = $this->clusteringAnalysisDatasetFactory->create($dataset);

        dump($clusteringAnalysisDataset);

        $io->success('OK');

        return Command::SUCCESS;
    }
}
