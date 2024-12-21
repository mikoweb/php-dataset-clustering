<?php

namespace App\UI\CLI;

use App\Application\Analytics\ClusteringAnalysisDatasetFactory;
use App\Application\ML\DatasetClusterer;
use App\Application\ML\ElbowPoint;
use App\Infrastructure\Reader\DatasetReader;
use noximo\PHPColoredAsciiLinechart\Colorizers\AsciiColorizer;
use noximo\PHPColoredAsciiLinechart\Linechart;
use noximo\PHPColoredAsciiLinechart\Settings;
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

        $io->info('Printing inertia value...');
        dump($clusterer->getInertia());
        $elbowPoint = ElbowPoint::find(array_values($clusterer->getInertia()));

        $settings = (new Settings())
            ->setHeight(60)
            ->setDecimals(0)
        ;

        $linechart = new Linechart();
        $linechart->setSettings($settings);

        foreach ($clusterer->getInertia() as $k => $inertia) {
            $linechart->addPoint((int) round($inertia / 100), (int) $k);
        }

        $linechart->addPoint(
            round($clusterer->getInertia()[$elbowPoint] / 100),
            $elbowPoint,
            [AsciiColorizer::RED],
            Linechart::CROSS,
        );

        $linechart->chart()->print();

        $io->success('OK');

        return Command::SUCCESS;
    }
}
