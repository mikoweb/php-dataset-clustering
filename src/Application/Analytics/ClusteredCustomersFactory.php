<?php

namespace App\Application\Analytics;

use App\Application\ML\ClusteringResult;
use App\Domain\ClusteredCustomer;
use App\UI\Dto\ClusteringAnalysisDto;

class ClusteredCustomersFactory
{
    /**
     * @param ClusteringAnalysisDto[] $dataset
     * @param ClusteringResult[]      $results
     *
     * @return ClusteredCustomer[]
     */
    public static function create(array $dataset, array $results): array
    {
        $clusteredCustomers = [];

        foreach ($dataset as $i => $data) {
            $clusteredCustomers[] = new ClusteredCustomer(
                $data->customerId,
                $data->recency,
                $data->frequency,
                $data->monetary,
                $results[$i]->clusterNumber,
            );
        }

        return $clusteredCustomers;
    }
}
