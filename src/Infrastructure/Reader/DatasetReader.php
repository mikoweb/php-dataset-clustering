<?php

namespace App\Infrastructure\Reader;

use App\Application\Cache\DatasetCacheInterface;
use App\Application\Path\AppPathResolver;
use App\UI\Dto\DatasetRowDto;
use Doctrine\Common\Collections\ArrayCollection;
use League\Csv\Reader;
use DateTimeImmutable;

use function Symfony\Component\String\u;

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
                    if (!empty($row['CustomerID']) && $row['Quantity'] > 0 && $row['UnitPrice'] > 0.0) {
                        $rows->add($this->createDto($index, $row));
                    }
                }

                $this->removeDuplicates($rows);

                return $rows;
            }
        );
    }

    /**
     * @param array<string, string> $data
     */
    private function createDto(int $index, array $data): DatasetRowDto
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

    /**
     * @param ArrayCollection<int, DatasetRowDto> $dataset
     */
    private function removeDuplicates(ArrayCollection $dataset): void
    {
        /** @var array<string, int[]> $rowContent */
        $rowContent = [];

        foreach ($dataset as $index => $row) {
            $content = u()
                ->append($row->customerId)
                ->append('_')
                ->append($row->invoiceNo)
                ->append('_')
                ->append($row->stockCode)
                ->append('_')
                ->append((string) $row->quantity)
                ->append('_')
                ->append($row->invoiceDate->format('Y-m-d H:i'))
                ->append('_')
                ->append((string) $row->unitPrice)
                ->append('_')
                ->toString();

            if (isset($rowContent[$content])) {
                $rowContent[$content][] = $index;
            } else {
                $rowContent[$content] = [$index];
            }
        }

        foreach ($rowContent as $indexes) {
            if (count($indexes) > 1) {
                foreach (array_slice($indexes, 1) as $index) {
                    $dataset->remove($index);
                }
            }
        }
    }
}
