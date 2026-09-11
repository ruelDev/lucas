/* eslint-disable react/prop-types */
export default function LoadingSkeleton({ rows = 5 }) {
    return (
        <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            {/* Header */}

            <div className="border-b border-gray-200 p-4">
                <div className="h-5 w-48 animate-pulse rounded bg-gray-200" />
            </div>

            {/* Rows */}

            <div className="divide-y divide-gray-200">
                {Array.from({
                    length: rows,
                }).map((_, index) => (
                    <div key={index} className="grid grid-cols-5 gap-6 p-6">
                        <div className="h-4 animate-pulse rounded bg-gray-200" />

                        <div className="h-4 animate-pulse rounded bg-gray-200" />

                        <div className="h-4 animate-pulse rounded bg-gray-200" />

                        <div className="h-4 animate-pulse rounded bg-gray-200" />

                        <div className="h-4 w-24 animate-pulse rounded-full bg-gray-200" />
                    </div>
                ))}
            </div>
        </div>
    );
}
