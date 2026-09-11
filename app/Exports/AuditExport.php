<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Services\AuditLogManagementExportService;

class AuditExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{

    protected $audits;
    protected $search;
    protected $sortBy;
    protected $sortDir;
    protected $dateFrom;
    protected $dateTo;
    protected $exportType;
    protected $moduleSelect;

    /**
     * @return \Illuminate\Support\Collection
     */

    public function __construct($exportType, $search = null, $sortBy = null, $sortDir = null, $dateFrom = null, $dateTo = null, $moduleSelect = null)
    {
        $this->search = $search;
        $this->sortBy = $sortBy;
        $this->sortDir = $sortDir;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->exportType = $exportType;
        $this->moduleSelect = $moduleSelect;
    }

    public function collection()
    {
        $auditLogExport = new AuditLogManagementExportService;

        return $auditLogExport->auditLogExportQuery($this->search, $this->sortBy, $this->sortDir, $this->dateFrom, $this->dateTo, $this->moduleSelect);
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Timestamp',
            'User',
            'Module',
            'Action',
            'IP Address',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0070C0'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        return [];
    }
}
