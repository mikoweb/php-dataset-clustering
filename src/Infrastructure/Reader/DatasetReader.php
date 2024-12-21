<?php

namespace App\Infrastructure\Reader;

use App\Application\Cache\DatasetCacheInterface;
use App\Application\Path\AppPathResolver;
use App\Module\Analytics\Application\Interaction\Query\GetSentimentScoreDistribution\GetSentimentScoreDistributionQuery;
use App\Module\Analytics\Application\Math\SentimentScoreThreshold;
use App\UI\Dto\DatasetRowDto;
use Doctrine\Common\Collections\ArrayCollection;
use League\Csv\Reader;
use DateTimeImmutable;

readonly class DatasetReader
{
    public function __construct(
        private AppPathResolver $appPathResolver,
        private DatasetCacheInterface $datasetCache,
    ) {
    }

    /**
     * @return ArrayCollection<int, DatasetRowDto>
     */
    public function read(): ArrayCollection
    {
        return $this->datasetCache->get(
            sprintf('%s_dataset', self::class),
            function () {
                $data = Reader::createFromPath($this->appPathResolver->getResourcesPath('dataset.csv'));

                $data
                    ->setHeaderOffset(0)
                    ->setDelimiter(';')
                ;

                $rows = new ArrayCollection();

                foreach ($data as $index => $row) {
                    $rows->add($this->createDto($index, $row));
                }

                return $rows;
            }
        );
    }

    /**
     * @param array<string, string> $data
     */
    public function createDto(int $index, array $data): DatasetRowDto
    {
        return new DatasetRowDto(
            index: $index,
            invoiceNo: $data['InvoiceNo'],
            stockCode: $data['StockCode'],
            description: $data['Description'],
            quantity: (int) $data['Quantity'],
            invoiceDate: DateTimeImmutable::createFromFormat('Y-m-d H:i', $data['InvoiceDate']),
            unitPrice: (float) str_replace(',', '.', $data['UnitPrice']),
            customerId: $data['CustomerID'],
            country: $data['Country'],
        );
    }
}
