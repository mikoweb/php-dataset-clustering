<?php

namespace App\Infrastructure\Reader;

use App\UI\Dto\DatasetRowDto;
use Doctrine\Common\Collections\ArrayCollection;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use DateTimeImmutable;

readonly class DatasetReader
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
    ) {
    }

    /**
     * @return ArrayCollection<int, DatasetRowDto>
     *
     * @throws UnavailableStream
     * @throws Exception
     */
    public function read(): ArrayCollection
    {
        $data = Reader::createFromPath(
            sprintf('%s/resources/dataset.csv', $this->parameterBag->get('kernel.project_dir'))
        );

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
