/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { format } from 'date-fns';
import { Calendar1 } from 'lucide-react';
import { useState } from 'react';

export function ArDate({ value, onChange }) {
    const [open, setOpen] = useState(false);
    const selectedDate = value ? new Date(value) : undefined;

    return (
        <>
            <Label htmlFor="arDate" className="text-xs after:ml-0.5 after:text-red-500 after:content-['*']">
                AR date
            </Label>
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        variant="outline"
                        id="arDate"
                        className={`mt-1.5 w-full justify-between font-normal ${!selectedDate && 'text-muted-foreground'}`}
                    >
                        {selectedDate ? format(selectedDate, 'MMMM dd, yyyy') : 'Pick a date'}
                        <Calendar1 className="size-4" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto overflow-hidden p-0" align="start">
                    <Calendar
                        className="sm:w-64"
                        mode="single"
                        selected={value}
                        captionLayout="dropdown"
                        onSelect={(date) => {
                            onChange(format(date, 'yyyy-MM-dd'));
                            setOpen(false);
                        }}
                    />
                </PopoverContent>
            </Popover>
        </>
    );
}
