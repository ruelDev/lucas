/* eslint-disable react/prop-types */
import { DataTableViewOptions } from '@/components/datatable/view-options';
import { DateRange } from '@/components/date-range';
import SearchInputWithLabel from '@/components/search-input-with-label';
import { Button } from '@/components/ui/button';
import { canAny } from '@/lib/can';
import { Download } from 'lucide-react';
import { SelectReceiptType } from './select-receipt-type';

export default function DataTableControls({
    hasRows,
    searchTerm,
    onSearchTermChange,
    dateFrom,
    dateTo,
    receiptType,
    onDateFromChange,
    onDateToChange,
    onReceiptTypeChange,
    onExport,
    table,
    columnLabels = {},
}) {
    return (
        <div className="mb-4 flex items-center justify-between gap-2">
            <div className="flex items-center gap-2">
                <SearchInputWithLabel value={searchTerm} onChange={onSearchTermChange} placeholder="Maker ID" />
                <DateRange dateFrom={dateFrom} dateTo={dateTo} onDateFromChange={onDateFromChange} onDateToChange={onDateToChange} label="AR Date" />
                <SelectReceiptType value={receiptType} onValueChange={onReceiptTypeChange} />
            </div>
            <div className="flex items-center gap-2">
                <DataTableViewOptions table={table} columnLabels={columnLabels} />
                {canAny(['out_collection_report.export']) && (
                    <div>
                        <Button disabled={!hasRows} onClick={onExport} variant="outline" className="submit-button">
                            <Download className="h-4 w-4" />
                            Export PDF
                        </Button>
                    </div>
                )}
            </div>
        </div>
    );
}
