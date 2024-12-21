<?php

namespace App\Infrastructure\Repository;

use App\UI\Dto\DatasetRowDto;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use UnexpectedValueException;

class ArrayDatasetRepository implements DatasetRepository
{
    /**
     * @var ArrayCollection<int, DatasetRowDto>|null
     */
    private ?ArrayCollection $sortedByInvoiceDate = null;

    /**
     * @var string[]|null
     */
    private ?array $customers = null;

    /**
     * @var array<string, DateTimeInterface>|null
     */
    private ?array $latestInvoiceDateGroupByCustomer = null;

    public function __construct(
        /**
         * @var ArrayCollection<int, DatasetRowDto>
         */
        private readonly ArrayCollection $dataset,
    ) {
    }

    public function getLatestInvoiceDate(): ?DateTimeInterface
    {
        return ($this->getSortedByInvoiceDate()->first() ?: null)?->invoiceDate;
    }

    /**
     * @return array<string, DateTimeInterface>
     */
    public function getLatestInvoiceDateGroupByCustomer(): array
    {
        if (is_null($this->latestInvoiceDateGroupByCustomer)) {
            $this->latestInvoiceDateGroupByCustomer = [];

            foreach ($this->getSortedByInvoiceDate() as $row) {
                if (!isset($this->latestInvoiceDateGroupByCustomer[$row->customerId])) {
                    $this->latestInvoiceDateGroupByCustomer[$row->customerId] = $row->invoiceDate;
                }
            }
        }

        return $this->latestInvoiceDateGroupByCustomer;
    }

    public function getDifferenceBetweenCustomerLastInvoiceAndLatestInvoiceDate(string $customerId): int
    {
        $customerLastInvoiceDate = $this->getLatestInvoiceDateGroupByCustomer()[$customerId] ?? null;

        if (is_null($customerLastInvoiceDate)) {
            throw new UnexpectedValueException(sprintf('Not found customer with id `%s`', $customerId));
        }

        $lastInvoiceDate = $this->getLatestInvoiceDate();
        $interval = $lastInvoiceDate->diff($customerLastInvoiceDate);

        return $interval->days;
    }

    public function countCustomerInvoices(string $customerId): int
    {
        $invoices = [];

        foreach ($this->dataset as $row) {
            if ($row->customerId === $customerId && !in_array($row->invoiceDate, $invoices)) {
                $invoices[] = $row->invoiceDate;
            }
        }

        return count($invoices);
    }

    public function sumCustomerTotalPurchase(string $customerId): float
    {
        $sum = 0.0;

        foreach ($this->dataset as $row) {
            if ($row->customerId === $customerId) {
                $sum += $row->totalPurchase;
            }
        }

        return $sum;
    }

    public function getCustomers(): array
    {
        if (is_null($this->customers)) {
            $this->customers = array_values(array_unique($this->dataset->map(
                fn (DatasetRowDto $row) => $row->customerId
            )->toArray()));
        }

        return $this->customers;
    }

    /**
     * @return ArrayCollection<int, DatasetRowDto>
     */
    private function getSortedByInvoiceDate(): ArrayCollection
    {
        if (is_null($this->sortedByInvoiceDate)) {
            $criteria = Criteria::create()
                ->orderBy(['invoiceDate' => Order::Descending])
            ;

            $this->sortedByInvoiceDate = $this->dataset->matching($criteria);
        }

        return $this->sortedByInvoiceDate;
    }
}
