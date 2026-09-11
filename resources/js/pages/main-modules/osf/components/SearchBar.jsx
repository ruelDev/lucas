/* eslint-disable react/prop-types */
import clsx from 'clsx';
import { AlertCircle, Loader2, RotateCcw, Search } from 'lucide-react';

export default function SearchBar({ fields = [], form, loading = false, validation = '', docked = false, onChange, onSearch, onReset }) {
    const handleKeyDown = (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            onSearch();
        }
    };

    return (
        <div className={clsx('w-full transition-all duration-300', docked ? 'sticky top-4 z-30' : '')}>
            <h2 className='text-slate-800 text-sm font-semibold dark:text-gray-300'>Search a Customer</h2>
            <div
                className={clsx(
                    'my-2 flex w-full flex-col rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 p-4 shadow-sm transition-all duration-300 sm:flex-row sm:items-stretch sm:gap-0 sm:p-3',
                    validation ? 'border-red-200 ring-2 ring-red-100' : '',
                )}
            >
                <div className="bg-brand-primary my-auto hidden h-10 w-10 shrink-0 items-center justify-center rounded-lg sm:flex">
                    <Search className="h-5 w-5 text-white" />
                </div>

                <div className="flex min-w-0 flex-1 flex-col gap-2 sm:flex-row sm:gap-0">
                    {fields.map((field, index) => (
                        <SearchSegment
                            key={field.key}
                            label={field.label}
                            optional={!field.required}
                            placeholder={field.placeholder}
                            value={form[field.key] ?? ''}
                            divider={index < fields.length - 1}
                            onKeyDown={handleKeyDown}
                            onChange={(value) => onChange(field.key, value)}
                        />
                    ))}
                </div>

                <div className="mt-2 flex w-full shrink-0 items-center gap-2 sm:mt-0 sm:w-auto sm:pl-2">
                    {docked && (
                        <button
                            type="button"
                            onClick={onReset}
                            disabled={loading}
                            title="Clear search"
                            className="group inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border dark:text-white border-gray-200 text-gray-500 transition-colors hover:bg-gray-50 hover:text-gray-700 dark:hover:text-gray-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <RotateCcw className="h-4 w-4 transition-transform duration-500 group-hover:-rotate-180" />
                        </button>
                    )}

                    <button
                        type="button"
                        onClick={onSearch}
                        disabled={loading}
                        className="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-lg bg-linear-to-r from-blue-900 to-blue-800 px-5 text-sm font-medium text-white transition duration-300 hover:from-blue-950 hover:to-blue-900 disabled:cursor-not-allowed disabled:opacity-60 sm:flex-none"
                    >
                        {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Search className="h-4 w-4" />}

                        <span>{loading ? 'Searching...' : 'Search'}</span>
                    </button>
                </div>
            </div>

            {validation && (
                <div className="mt-3 inline-flex max-w-full items-center gap-2 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs text-red-600">
                    <AlertCircle className="h-3.5 w-3.5 shrink-0" />
                    <span>{validation}</span>
                </div>
            )}
        </div>
    );
}

function SearchSegment({ label, optional = false, value, placeholder, divider = false, onChange, onKeyDown }) {
    return (
        <div
            className={clsx(
                'flex flex-1 flex-col justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-800 sm:min-w-40 sm:rounded-none sm:border-0 sm:bg-transparent sm:py-0.5',
                divider ? 'sm:border-r sm:border-gray-200' : '',
            )}
        >
            <label className="flex items-center gap-1 text-xs font-medium dark:text-gray-300 text-gray-500">
                {label}

                {optional && <span className="text-gray-400">(optional)</span>}
            </label>

            <input
                type="text"
                value={value}
                placeholder={placeholder}
                autoComplete="off"
                onChange={(event) => onChange(event.target.value)}
                onKeyDown={onKeyDown}
                className="mt-0.5 w-full rounded-md bg-transparent py-0.5 text-sm text-gray-900 transition-colors outline-none placeholder:text-gray-400 dark:placeholder:text-gray-600 sm:focus:text-gray-900 dark:text-gray-200"
            />
        </div>
    );
}
