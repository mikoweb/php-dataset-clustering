<?php

namespace App\Domain;

readonly class FeaturesMean
{
    public function __construct(
        public int $clusterNumber,
        public int $n,
        public float $recencyMean,
        public float $frequencyMean,
        public float $monetaryMean,
    ) {
    }
}
