/* eslint-disable react/prop-types */
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';
import { Calendar, Home, User } from 'lucide-react';

export default function CustomerCard({ customer, loading }) {
    if (loading) {
        return (
            <div className="w-full rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div className="flex items-start gap-5">
                    <Skeleton className="h-16 w-16 rounded-full" />
                    <div className="flex-1 space-y-3">
                        <Skeleton className="h-6 w-48" />
                        <Skeleton className="h-4 w-32" />
                        <div className="flex gap-6 pt-1">
                            <Skeleton className="h-4 w-36" />
                            <Skeleton className="h-4 w-52" />
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    if (!customer) return null;

    const fullName = [customer.first_name, customer.middle_name, customer.last_name].filter(Boolean).join(' ');
    const initials = [customer.first_name?.[0], customer.last_name?.[0]].filter(Boolean).join('').toUpperCase();

    return (
        <div className="w-full rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div className="h-2 w-full rounded-t-xl bg-gradient-to-r from-blue-900 to-blue-700" />
            <div className="flex flex-col gap-5 p-6 sm:flex-row sm:items-start">
                <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-800 to-blue-600 text-xl font-bold text-white shadow-md">
                    {initials || <User className="h-7 w-7" />}
                </div>
                <div className="flex-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <h2 className="text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{fullName}</h2>
                        <Badge className="bg-blue-100 px-2 py-0.5 text-[11px] font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 hover:text-white">
                            Customer
                        </Badge>
                    </div>
                    <div className="mt-1 flex flex-wrap gap-x-1 text-sm text-gray-500 dark:text-gray-400">
                        <span className="font-medium text-gray-700 dark:text-gray-300">{customer.first_name}</span>
                        {customer.middle_name && (<><span>·</span><span>{customer.middle_name}</span></>)}
                        <span>·</span>
                        <span className="font-medium text-gray-700 dark:text-gray-300">{customer.last_name}</span>
                    </div>
                    <div className="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:gap-x-6 sm:gap-y-2">
                        <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <Calendar className="h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400" />
                            <span>
                                <span className="font-medium text-gray-700 dark:text-gray-300">Date of Birth: </span>
                                {customer.date_of_birth ?? '—'}
                            </span>
                        </div>
                        <div className="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <Home className="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400" />
                            <span>
                                <span className="font-medium text-gray-700 dark:text-gray-300">Address: </span>
                                {customer.address ?? '—'}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}