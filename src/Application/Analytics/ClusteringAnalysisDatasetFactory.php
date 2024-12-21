<?php

namespace App\Application\Analytics;

use App\Application\Cache\DatasetCacheInterface;
use App\Infrastructure\Repository\ArrayDatasetRepository;
use App\UI\Dto\ClusteringAnalysisDto;
use App\UI\Dto\DatasetRowDto;
use Doctrine\Common\Collections\ArrayCollection;

readonly class ClusteringAnalysisDatasetFactory
{
    public function __construct(
        private DatasetCacheInterface $datasetCache,
    ) {
    }

    /**
     * @param ArrayCollection<int, DatasetRowDto> $dataset
     *
     * @return ClusteringAnalysisDto[]
     */
    public function create(ArrayCollection $dataset): array
    {
        return $this->datasetCache->get(
            sprintf('%s_dataset', self::class),
            function () use ($dataset) {
                $repository = new ArrayDatasetRepository($dataset);
                $clusteringAnalysisDataset = [];

                foreach ($repository->getCustomers() as $customerId) {
                    $clusteringAnalysisDataset[] = new ClusteringAnalysisDto(
                        customerId: $customerId,
                        recency: $repository->getDifferenceBetweenCustomerLastInvoiceAndLatestInvoiceDate($customerId),
                        frequency: $repository->countCustomerInvoices($customerId),
                        monetary: $repository->sumCustomerTotalPurchase($customerId),
                    );
                }

                return $clusteringAnalysisDataset;
            }
        );
    }
}
