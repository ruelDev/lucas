/* eslint-disable react/prop-types */
import { RotateCcw, SearchX } from 'lucide-react';

export default function EmptyState({ title = 'No records found', message = 'Try adjusting your search criteria.', onReset }) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white px-8 py-16 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div className="bg-brand-primary/10 mx-auto flex h-20 w-20 items-center justify-center rounded-full">
                <SearchX className="text-brand-primary h-10 w-10" />
            </div>

            <h2 className="mt-6 text-xl font-semibold text-gray-900 dark:text-gray-100">{title}</h2>

            <p className="mx-auto mt-3 max-w-md text-sm text-gray-500 dark:text-gray-300">{message}</p>

            <button
                onClick={onReset}
                className="mt-8 inline-flex items-center gap-2 rounded-lg bg-linear-to-r from-blue-900 to-blue-800 px-5 py-2.5 text-sm font-medium text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
            >
                <RotateCcw className="h-4 w-4" />
                Start New Search
            </button>
        </div>
    );
}
