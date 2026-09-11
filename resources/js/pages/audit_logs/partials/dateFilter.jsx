/* eslint-disable react/prop-types */
import { Input } from '@/components/ui/input';

export default function AuditDateFilter({ setDateRange }) {
    return (
        <div className="space-y-4">
            <div className="flex items-center gap-2">
                <Input
                    type="date"
                    value="dateRange.from"
                    onChange={(e) => setDateRange((prev) => ({ ...prev, from: e.target.value }))}
                    className="max-w-40"
                    placeholder="From"
                />
                <Input
                    type="date"
                    value="dateRange.to"
                    onChange={(e) => setDateRange((prev) => ({ ...prev, to: e.target.value }))}
                    className="max-w-40"
                    placeholder="To"
                />
            </div>
        </div>
    );
}
