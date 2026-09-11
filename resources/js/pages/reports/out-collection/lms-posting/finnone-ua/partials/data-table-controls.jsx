/* eslint-disable react/prop-types */
import { DataTableViewOptions } from '@/components/datatable/view-options';
import { DateRange } from '@/components/date-range';
import SearchInputWithLabel from '@/components/search-input-with-label';
import { Button } from '@/components/ui/button';
import { canAny } from '@/lib/can';
import { Download } from 'lucide-react';

export default function DataTableControls({
    hasRows,
    searchTerm,
    onSearchTermChange,
    dateFrom,
    dateTo,
    onDateFromChange,
    onDateToChange,
    onExport,
    table,
    columnLabels = {},
}) {
    return (
        <div className="mb-4 flex items-center justify-between gap-2">
            <div className="flex items-center gap-2">
                <SearchInputWithLabel value={searchTerm} onChange={onSearchTermChange} placeholder="Maker ID" />
                <DateRange dateFrom={dateFrom} dateTo={dateTo} onDateFromChange={onDateFromChange} onDateToChange={onDateToChange} label="AR Date" />
            </div>
            <div className="flex items-center gap-2">
                <DataTableViewOptions table={table} columnLabels={columnLabels} />
                {canAny(['lms_posting_report.export']) && (
                    <div>
                        <Button disabled={!hasRows} onClick={onExport} variant="outline" className="submit-button">
                            <Download className="h-4 w-4" />
                            Export Excel
                        </Button>
                    </div>
                )}
            </div>
        </div>
    );
}
