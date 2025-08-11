<?php

namespace Scrapify\ApiTools\Exports;

class DynamicExport
{
    protected array $rows;
    protected array $headings;

    public function __construct(array $rows, array $headings)
    {
        $this->rows = $rows;
        $this->headings = $headings;
    }

    public function getHeadings(): array
    {
        return $this->headings;
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}
