/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ChevronDownIcon } from 'lucide-react';
import { useState } from 'react';

export default function DateFilter({ dateFrom, onDateFromChange, dateTo, onDateToChange }) {
    const [dateOpen, setDateOpen] = useState(false);

    const formatDisplay = () => {
        if (!dateFrom || !(dateFrom instanceof Date)) return 'Select Date Range';
        if (!dateTo || !(dateTo instanceof Date)) return `${dateFrom.toLocaleDateString()} → Select end date...`;

        return `${dateFrom.toLocaleDateString()} - ${dateTo.toLocaleDateString()}`;
    };

    const handleOpenChange = (open) => {
        setDateOpen(open);

        if (!open && !(dateFrom instanceof Date && dateTo instanceof Date)) {
            onDateFromChange(null);
            onDateToChange(null);
        }
    };

    const handleSelect = (range) => {
        if (!range) {
            onDateFromChange(null);
            onDateToChange(null);
            return;
        }

        const from = range.from || null;
        const to = range.to || null;

        const isNewFrom = from && dateFrom && from.toDateString() !== dateFrom.toDateString();
        const isFromAfterTo = from && dateTo instanceof Date && from > dateTo;

        if (isNewFrom || isFromAfterTo) {
            onDateFromChange(from);
            onDateToChange(null);
            return;
        }

        onDateFromChange(from);
        onDateToChange(to);

        if (from && to && from.toDateString() !== to.toDateString()) {
            setDateOpen(false);
        }
    };

    return (
        <div className="relative mx-2 flex flex-col items-start">
            <Label className="mb-1 text-sm text-gray-600">Date Range:</Label>

            <Popover open={dateOpen} onOpenChange={handleOpenChange}>
                <PopoverTrigger asChild>
                    <Button variant="outline" className="w-[280px] justify-between font-normal">
                        {formatDisplay()}
                        <ChevronDownIcon />
                    </Button>
                </PopoverTrigger>

                <PopoverContent className="w-auto p-0" align="center">
                    <Calendar
                        mode="range"
                        className="data-[selected=true]:text-blue-600 xl:w-[500px]"
                        selected={{
                            from: dateFrom || undefined,
                            to: dateTo || undefined,
                        }}
                        onSelect={handleSelect}
                        numberOfMonths={2}
                        captionLayout="dropdown"
                    />
                </PopoverContent>
            </Popover>
        </div>
    );
}
