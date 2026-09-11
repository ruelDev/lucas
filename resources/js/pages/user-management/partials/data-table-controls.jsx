/* eslint-disable react/prop-types */
import SearchInputWithLabel from '@/components/search-input-with-label';
import CreateUserButton from '../dialog/create-btn';
import ExportDialog from '../dialog/export';
import RoleSelect from '../filters/role-select';
import StatusSelect from '../filters/status-select';

export default function DataTableControls({ roleSelect, onRoleSelectChange, searchTerm, onSearchChange, statusSelect, onStatusChange, sorting }) {
    return (
        <div className="mb-4 flex items-center justify-between gap-2">
            <div className="flex items-center space-x-6">
                <SearchInputWithLabel value={searchTerm} onChange={onSearchChange} placeholder="Search here..." />
                <RoleSelect roleSelect={roleSelect} onRoleSelectChange={onRoleSelectChange} />
                <StatusSelect statusSelect={statusSelect} onStatusChange={onStatusChange} />
            </div>
            <div className="flex flex-1 items-center justify-end space-x-2">
                <ExportDialog searchVal={searchTerm} sorting={sorting} />
                <CreateUserButton />
            </div>
        </div>
    );
}
