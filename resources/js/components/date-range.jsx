/* eslint-disable react/prop-types */
'use client';

import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Field, FieldLabel } from '@/components/ui/field';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { format } from 'date-fns';
import { CalendarIcon } from 'lucide-react';
import * as React from 'react';

export function DateRange({ dateFrom, dateTo, onDateFromChange, onDateToChange, label }) {
    const [open, setOpen] = React.useState(false);

    const selectedDateRange = () => {
        if (dateFrom && dateTo) {
            return `${format(new Date(dateFrom), 'LLL dd, y')} - ${format(new Date(dateTo), 'LLL dd, y')}`;
        }
        if (dateFrom) {
            return format(new Date(dateFrom), 'LLL dd, y');
        }
        return null;
    };

    return (
        <Field className="text-muted-foreground flex w-60 gap-1">
            <FieldLabel>{label}:</FieldLabel>
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button variant="outline" id="date-picker-range" className="justify-start px-2.5 text-neutral-500">
                        <CalendarIcon />
                        {selectedDateRange() ?? <span>Pick {label}</span>}
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                        className="w-md"
                        mode="range"
                        defaultMonth={dateFrom}
                        selected={{
                            from: dateFrom || undefined,
                            to: dateTo || undefined,
                        }}
                        onSelect={(newDate) => {
                            const from = newDate?.from ? format(newDate.from, 'yyyy-MM-dd') : null;
                            const to = newDate?.to ? format(newDate.to, 'yyyy-MM-dd') : null;

                            onDateFromChange?.(from);
                            onDateToChange?.(to, from);

                            if (newDate?.from && newDate?.to) {
                                setOpen(false);
                            }
                        }}
                        numberOfMonths={2}
                        classNames={{
                            month_caption: 'flex h-[--cell-size] w-full items-center justify-center px-[--cell-size] pt-1.5',
                        }}
                    />
                </PopoverContent>
            </Popover>
        </Field>
    );
}
