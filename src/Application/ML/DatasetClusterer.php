<?php

namespace App\Application\ML;

use App\UI\Dto\ClusteringAnalysisDto;
use MathPHP\Exception\BadDataException;
use MathPHP\Statistics\Distance;
use Rubix\ML\Clusterers\KMeans;
use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\ColumnPicker;
use Rubix\ML\Transformers\ZScaleStandardizer;

class DatasetClusterer
{
    private ?Dataset $dataset  = null;

    /**
     * @var array<int, ClusteringResult[]>
     */
    private array $results = [];

    /**
     * @var array<int, float>
     */
    private array $inertia = [];

    /**
     * @param ClusteringAnalysisDto[] $arrayDataset
     * @param array<int<0, 1>, int>   $clustersRange
     *
     * @return array<int, ClusteringResult[]>
     *
     * @throws BadDataException
     */
    public function clusterize(array $arrayDataset, array $clustersRange): array
    {
        $this->dataset = $this->createDataset($arrayDataset);
        $this->normalizeDataset($this->dataset);
        /** @var array<int, ClusteringResult[]> $resultsByK */
        $resultsByK = [];

        for ($k = $clustersRange[0]; $k <= $clustersRange[1]; ++$k) {
            $estimator = new KMeans($k);
            $estimator->train($this->dataset);
            /** @var ClusteringResult[] $results */
            $results = [];

            foreach ($estimator->predict($this->dataset) as $i => $clusterNumber) {
                $results[] = new ClusteringResult(
                    $arrayDataset[$i]->customerId,
                    $clusterNumber,
                    $estimator->centroids(),
                );
            }

            $resultsByK[$k] = $results;
        }

        $this->results = $resultsByK;
        $this->calculateInertia();

        return $resultsByK;
    }

    /**
     * @return array<int, ClusteringResult[]>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @return array<int, float>
     */
    public function getInertia(): array
    {
        return $this->inertia;
    }

    /**
     * @param ClusteringAnalysisDto[] $arrayDataset
     */
    private function createDataset(array $arrayDataset): Dataset
    {
        return Unlabeled::fromIterator(new ColumnPicker(
            json_decode(json_encode($arrayDataset), true),
            ['recency', 'frequency', 'monetary'],
        ));
    }

    private function normalizeDataset(Dataset $dataset): void
    {
        $dataset->apply(new ZScaleStandardizer(true));
    }

    /**
     * @throws BadDataException
     */
    private function calculateInertia(): void
    {
        $this->inertia = [];

        foreach ($this->results as $k => $results) {
            $this->inertia[$k] = 0.0;

            foreach ($results as $i => $result) {
                $point = $this->dataset[$i];
                $centroid = $result->centroids[$result->clusterNumber];

                $this->inertia[$k] += (Distance::euclidean($point, $centroid) ** 2);
            }
        }
    }
}
