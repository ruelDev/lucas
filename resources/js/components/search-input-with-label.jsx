/* eslint-disable react/prop-types */
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { Search } from 'lucide-react';

export default function SearchInputWithLabel({ value, onChange, placeholder, className }) {
    return (
        <div className="text-muted-foreground relative max-w-sm flex-1">
            <Search className="absolute bottom-3 left-3 h-4 w-4" />
            <Field className="flex gap-1">
                <FieldLabel htmlFor="search">Search by:</FieldLabel>
                <Input
                    id="search"
                    type="search"
                    placeholder={placeholder}
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    className={cn('pl-8', className)}
                />
            </Field>
        </div>
    );
}
