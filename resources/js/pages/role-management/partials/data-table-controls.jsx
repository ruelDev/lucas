/* eslint-disable react/prop-types */
import SearchInputWithLabel from '@/components/search-input-with-label';
import CreateRoleButton from '../dialog/create-btn';

export default function DataTableControls({ searchTerm, onSearchChange }) {
    return (
        <div className="mb-4 flex items-center justify-between gap-2">
            <div className="flex flex-1 items-center space-x-6">
                <SearchInputWithLabel value={searchTerm} onChange={onSearchChange} placeholder="Search here..." />
            </div>
            <div className="flex flex-1 items-center justify-end space-x-2">
                <CreateRoleButton />
            </div>
        </div>
    );
}
