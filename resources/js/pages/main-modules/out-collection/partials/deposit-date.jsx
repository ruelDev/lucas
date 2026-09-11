/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { format } from 'date-fns';
import { Calendar1 } from 'lucide-react';
import * as React from 'react';

export default function DepositDate({ value, onChange }) {
    const [open, setOpen] = React.useState(false);

    const selectedDate = value ? new Date(value) : undefined;

    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(23, 59, 59, 999);

    return (
        <>
            <Label className="text-muted-foreground text-xs font-medium tracking-wide uppercase after:ml-0.5 after:text-red-500 after:content-['*']">
                Deposit date
            </Label>
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button variant="outline" id="date" className="justify-between font-normal">
                        {selectedDate ? format(selectedDate, 'MMMM dd, yyyy') : 'Select Deposit Date'}
                        <Calendar1 className="h-4 w-4 opacity-50" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto overflow-hidden p-0" align="start">
                    <Calendar
                        className="sm:w-64"
                        mode="single"
                        selected={value}
                        captionLayout="dropdown"
                        disabled={{ after: tomorrow }}
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
