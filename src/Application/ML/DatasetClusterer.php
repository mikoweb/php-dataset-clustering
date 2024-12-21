<?php

namespace App\Application\ML;

use App\UI\Dto\ClusteringAnalysisDto;
use Rubix\ML\Clusterers\KMeans;
use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\ColumnPicker;
use Rubix\ML\Transformers\ZScaleStandardizer;

class DatasetClusterer
{
    /**
     * @param ClusteringAnalysisDto[] $arrayDataset
     * @param array<int<0, 1>, int>   $clustersRange
     *
     * @return array<int, ClusteringResult[]>
     */
    public function clusterize(array $arrayDataset, array $clustersRange): array
    {
        $dataset = $this->createDataset($arrayDataset);
        $this->normalizeDataset($dataset);
        /** @var array<int, ClusteringResult[]> $resultsByK */
        $resultsByK = [];

        for ($k = $clustersRange[0]; $k <= $clustersRange[1]; ++$k) {
            $estimator = new KMeans($k);
            $estimator->train($dataset);
            /** @var ClusteringResult $results */
            $results = [];

            foreach ($estimator->predict($dataset) as $i => $clusterNumber) {
                $results[] = new ClusteringResult(
                    $arrayDataset[$i]->customerId,
                    $clusterNumber,
                    $estimator->centroids(),
                );
            }

            $resultsByK[] = $results;
        }

        return $resultsByK;
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
}
