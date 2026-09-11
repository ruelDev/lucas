/* eslint-disable react/prop-types */
import EmptyState from './EmptyState';
import LoadingSkeleton from './LoadingSkeleton';
import ResultsCards from './ResultsCards';
import ResultsTable from './ResultsTable';

export default function SearchResults({
    loading,
    hasSearched,
    results,
    columns,
    lastQuery = '',
    onReset,
    onSelect,
}) {
    if (loading) {
        return <LoadingSkeleton />;
    }

    if (hasSearched && results.length === 0) {
        return (
            <EmptyState
                message={
                    lastQuery
                        ? `We couldn't find anyone matching "${lastQuery}". Check the spelling or try fewer details.`
                        : 'Try adjusting your search criteria.'
                }
                onReset={onReset}
            />
        );
    }

    if (!hasSearched) {
        return null;
    }
     
    return (
        <div className="w-full">
            <style>
                {`
                    @keyframes fadeSlideUp {
                        from {
                            opacity: 0;
                            transform: translateY(12px);
                        }

                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                `}
            </style>

            <div className="mb-4 mt-6 flex flex-col gap-1">
                <h2 className="text-slate-800 text-sm font-semibold dark:text-gray-300">Search Results</h2>

                <p className="text-xs text-gray-500 dark:text-gray-400">
                    {results.length} {results.length === 1 ? 'match' : 'matches'}
                    {lastQuery ? ` for "${lastQuery}"` : ''}
                </p>
            </div>

            <div className="block lg:hidden">
                <ResultsCards data={results} onSelect={onSelect} />
            </div>

            <div className="hidden lg:block">
                <ResultsTable columns={columns} data={results} />
            </div>
        </div>
    );
}
