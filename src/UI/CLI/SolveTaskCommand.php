<?php

namespace App\UI\CLI;

use App\Application\Analytics\ClusteringAnalysisDatasetFactory;
use App\Application\ML\DatasetClusterer;
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

        $clustersRange = [1, 10];
        $clusterer = new DatasetClusterer();

        $io->info(sprintf('Clustering for K range<%d, %d>...', $clustersRange[0], $clustersRange[1]));
        $clusterer->clusterize($clusteringAnalysisDataset, $clustersRange);
        dump($clusterer->getInertia());

        $io->success('OK');

        return Command::SUCCESS;
    }
}
