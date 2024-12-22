<?php

namespace App\Domain;

readonly class ClusteredCustomer
{
    public function __construct(
        public string $customerId,
        public int $recency,
        public int $frequency,
        public float $monetary,
        public int $clusterNumber,
    ) {
    }
}
