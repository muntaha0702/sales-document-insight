<?php

// app/Imports/CsvDataImporter.php
namespace App\Imports;

use App\Models\CsvData;
use Spatie\SimpleExcel\SimpleExcelReader;

class CsvImport
{
    public function import(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: $filePath");
        }

        $rows = SimpleExcelReader::create($filePath)
            ->useDelimiter(',')
            ->getRows();

        $rows->each(function (array $row) {
            CsvData::create([
                'category' => $row['category'] ?? $row[0] ?? null,
                'value' => $row['value'] ?? $row[1] ?? null,
            ]);
        });
    }
}