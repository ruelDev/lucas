/* eslint-disable react/prop-types */
import SearchInputWithLabel from '@/components/search-input-with-label';
import { Button } from '@/components/ui/button';
import { canAny } from '@/lib/can';
import { Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

export const DataTableControls = ({ searchTerm, onSearchChange }) => {
    return (
        <div className="mb-4 flex items-center gap-2">
            <div className="flex flex-1 items-center justify-between space-x-2">
                <SearchInputWithLabel value={searchTerm} onChange={onSearchChange} placeholder="Search here..." />
                {!canAny(['out_collection.authorize']) && (
                    <Button asChild>
                        <Link href={route('out-collection.create')} className="submit-button">
                            <Plus className="size-4" /> Add New Deposit
                        </Link>
                    </Button>
                )}
            </div>
        </div>
    );
};
