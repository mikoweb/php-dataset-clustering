<?php

namespace App\UI\Dto;

readonly class ClusteringAnalysisDto
{
    public function __construct(
        public string $customerId,
        public int $recency,
        public int $frequency,
        public float $monetary,
    ) {
    }
}
