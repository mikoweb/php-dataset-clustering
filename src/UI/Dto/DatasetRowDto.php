<?php

namespace App\UI\Dto;

use DateTimeImmutable;

readonly class DatasetRowDto
{
    public function __construct(
        public int $index,
        public string $invoiceNo,
        public string $stockCode,
        public string $description,
        public int $quantity,
        public DateTimeImmutable $invoiceDate,
        public float $unitPrice,
        public string $customerId,
        public string $country,
    ) {
    }
}
