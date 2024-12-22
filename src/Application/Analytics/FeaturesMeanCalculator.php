<?php

namespace App\Application\Analytics;

use App\Application\ML\ClusteringResult;
use App\Domain\FeaturesMean;
use App\UI\Dto\ClusteringAnalysisDto;
use MathPHP\Exception\BadDataException;
use MathPHP\Statistics\Average;

class FeaturesMeanCalculator
{
    /**
     * @param ClusteringAnalysisDto[] $dataset
     * @param ClusteringResult[]      $results
     *
     * @return FeaturesMean[]
     *
     * @throws BadDataException
     */
    public function calculate(array $dataset, array $results): array
    {
        $clusters = [];

        foreach ($results as $i => $result) {
            if (!isset($clusters[$result->clusterNumber])) {
                $clusters[$result->clusterNumber] = [
                    'clusterNumber' => $result->clusterNumber,
                    'recency' => [],
                    'frequency' => [],
                    'monetary' => [],
                ];
            }

            $clusters[$result->clusterNumber]['recency'][] = $dataset[$i]->recency;
            $clusters[$result->clusterNumber]['frequency'][] = $dataset[$i]->frequency;
            $clusters[$result->clusterNumber]['monetary'][] = $dataset[$i]->monetary;
        }

        /** @var FeaturesMean[] $means */
        $means = [];

        foreach ($clusters as $clusterNumber => $values) {
            $means[] = new FeaturesMean(
                $clusterNumber,
                count($values['recency']),
                Average::mean($values['recency']),
                Average::mean($values['frequency']),
                Average::mean($values['monetary']),
            );
        }

        return $means;
    }
}
