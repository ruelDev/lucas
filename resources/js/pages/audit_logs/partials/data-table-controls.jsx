/* eslint-disable react/prop-types */
import SearchInputWithLabel from '@/components/search-input-with-label';
import DateFilter from '../filters/date-filter';
import ModuleSelect from '../filters/module-filter';
import ExportDialog from './export';

export default function DataTableControls({
    searchTerm,
    onSearchChange,
    moduleSelect,
    onModuleSelectChange,
    dateFrom,
    onDateFromChange,
    dateTo,
    onDateToChange,
    sorting,
}) {
    return (
        <div className="mb-4 flex items-center justify-between gap-2">
            <div className="flex items-center space-x-6">
                <SearchInputWithLabel value={searchTerm} onChange={onSearchChange} placeholder="Search here..." />
                <ModuleSelect moduleSelect={moduleSelect} onModuleSelectChange={onModuleSelectChange} />
                <DateFilter dateFrom={dateFrom} onDateFromChange={onDateFromChange} dateTo={dateTo} onDateToChange={onDateToChange} />
            </div>
            <div className="flex items-center justify-end space-x-2">
                <ExportDialog searchVal={searchTerm} sorting={sorting} dateFrom={dateFrom} dateTo={dateTo} moduleSelect={moduleSelect} />
            </div>
        </div>
    );
}
