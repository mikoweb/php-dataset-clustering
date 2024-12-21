<?php

namespace App\Application\ML;

readonly class ClusteringResult
{
    public function __construct(
        public string $customerId,
        public int $clusterNumber,

        /**
         * @var array<int, array<int, float>>
         */
        public array $centroids,
    ) {
    }
}
