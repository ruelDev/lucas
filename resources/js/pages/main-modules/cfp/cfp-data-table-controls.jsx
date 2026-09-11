/* eslint-disable react/prop-types */
import SearchInputWithLabel from '@/components/search-input-with-label';

export const DataTableControls = ({ searchTerm, onSearchChange, onCreateAction }) => {
    return (
        <div className="mb-4 flex items-center justify-between">
            <div className="flex flex-1 items-center space-x-2">
                <SearchInputWithLabel value={searchTerm} onChange={onSearchChange} placeholder="Search Agreement Number..." />
            </div>
            <div className="flex items-center gap-2">{onCreateAction}</div>
        </div>
    );
};
