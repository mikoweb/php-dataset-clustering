<?php

namespace App\UI\CLI;

use App\Application\Analytics\ClusteredCustomersFactory;
use App\Application\Analytics\ClusteringAnalysisDatasetFactory;
use App\Application\Analytics\FeaturesMeanCalculator;
use App\Application\ML\DatasetClusterer;
use App\Application\ML\ElbowPoint;
use App\Application\Path\AppPathResolver;
use App\Infrastructure\Reader\DatasetReader;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\UnavailableStream;
use League\Csv\Writer;
use MathPHP\Exception\BadDataException;
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
        private readonly AppPathResolver $appPathResolver,
    ) {
        parent::__construct();
    }

    /**
     * @throws BadDataException
     * @throws UnavailableStream
     * @throws CannotInsertRecord
     * @throws Exception
     */
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
        $elbowPoint = ElbowPoint::find(array_values($clusterer->getInertia()));
        $inertia = $clusterer->getInertia();

        dump($inertia);
        $io->writeln('');
        $this->printChartElbowMethod($elbowPoint, $inertia);

        $io->writeln('');
        $io->success(sprintf('Optimal k: %d', $elbowPoint));

        $calculator = new FeaturesMeanCalculator();

        $io->info('Features mean:');
        dump($calculator->calculate($clusteringAnalysisDataset, $clusterer->getResults()[$elbowPoint]));
        $io->writeln('');
        $io->writeln('');

        $clusteredCustomersFilename = $this->appPathResolver->getResourcesPath('clustered_customers.csv');
        $clusteredCustomers =  json_decode(json_encode(ClusteredCustomersFactory::create(
            $clusteringAnalysisDataset,
            $clusterer->getResults()[$elbowPoint],
        )), true);

        $writer = Writer::createFromPath($clusteredCustomersFilename, 'w+');
        $writer->insertOne(array_keys($clusteredCustomers[0]));

        foreach ($clusteredCustomers as $clusteredCustomer) {
            $writer->insertOne(array_values($clusteredCustomer));
        }

        $io->success(sprintf('Clustered customers file: %s', $clusteredCustomersFilename));

        return Command::SUCCESS;
    }

    /**
     * @param array<int, float> $inertia
     */
    private function printChartElbowMethod(int $elbowPoint, array $inertia): void
    {
        $settings = (new Settings())
            ->setHeight(60)
            ->setDecimals(0)
        ;

        $linechart = new Linechart();
        $linechart->setSettings($settings);

        foreach ($inertia as $k => $value) {
            $linechart->addPoint((int) round($value / 100), $k);
        }

        $linechart->addPoint(
            (int) round($inertia[$elbowPoint] / 100),
            $elbowPoint,
            [AsciiColorizer::RED],
            Linechart::CROSS,
        );

        $linechart->chart()->print();
    }
}
