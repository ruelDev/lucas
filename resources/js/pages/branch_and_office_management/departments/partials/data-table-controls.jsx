/* eslint-disable react/prop-types */
import SearchInputWithLabel from '@/components/search-input-with-label';
import CreateDepartmentDialog from '../dialog/create';
import StatusSelect from '../filters/status-select';

export default function DataTableControls({ searchTerm, onSearchChange, statusSelect, onStatusChange }) {
    return (
        <div className="mb-4 flex items-center justify-between gap-2">
            <div className="flex items-center space-x-6">
                <SearchInputWithLabel value={searchTerm} onChange={onSearchChange} placeholder="Search here..." />
                <StatusSelect statusSelect={statusSelect} onStatusChange={onStatusChange} />
            </div>
            <div className="flex flex-1 items-center justify-end space-x-2">
                <CreateDepartmentDialog />
            </div>
        </div>
    );
}
