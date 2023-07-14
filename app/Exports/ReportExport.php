<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
//新增
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromCollection, WithColumnWidths, WithStyles
{
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }


    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true, 'size' => 13]],

            // Styling a specific cell by coordinate.
            // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }

    //寬度
    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 17,
            'C' => 10,
            'D' => 22,
            'E' => 15,
            'F' => 32,
            'G' => 28,
            'H' => 40,
            'I' => 9,
            'J' => 16,
            'K' => 14,
            'L' => 14,
            'M' => 16,
            'N' => 20,
        ];
    }
}
