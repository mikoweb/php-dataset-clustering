<?php

namespace App\Infrastructure\Repository;

use DateTimeInterface;

interface DatasetRepository
{
    public function getLatestInvoiceDate(): ?DateTimeInterface;

    /**
     * @return array<string, DateTimeInterface>
     */
    public function getLatestInvoiceDateGroupByCustomer(): array;
    public function getDifferenceBetweenCustomerLastInvoiceAndLatestInvoiceDate(string $customerId): int;
    public function countCustomerInvoices(string $customerId): int;
    public function sumCustomerTotalPurchase(string $customerId): float;

    /**
     * @return string[]
     */
    public function getCustomers(): array;
}
