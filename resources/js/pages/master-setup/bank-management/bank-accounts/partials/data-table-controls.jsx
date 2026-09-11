/* eslint-disable react/prop-types */
import SearchInputWithLabel from '@/components/search-input-with-label';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

export const DataTableControls = ({ searchTerm, onSearchChange, bank }) => {
    return (
        <div className="mb-4 flex items-center gap-2">
            <div className="flex flex-1 items-center justify-between space-x-2">
                <SearchInputWithLabel value={searchTerm} onChange={onSearchChange} placeholder="Search here..." />
                <div>
                    <Button variant="ghost" className="submit-button" asChild>
                        <Link href={route('bank-management.bank-accounts.create', { bank: bank.id })} title="Add bank">
                            <Plus className="h-4 w-4" />
                            Add new account
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    );
};
