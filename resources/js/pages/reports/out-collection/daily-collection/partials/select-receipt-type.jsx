/* eslint-disable react/prop-types */
import { Field, FieldLabel } from '@/components/ui/field';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export const SelectReceiptType = ({ value, onValueChange }) => {
    const receipts = [
        { value: 'QR', label: 'Quick Receipt' },
        { value: 'UA', label: 'Unapplied Receipt' },
    ];
    return (
        <Field className="text-muted-foreground flex w-60 gap-1">
            <FieldLabel>Receipt Type:</FieldLabel>
            <Select value={value} onValueChange={onValueChange}>
                <SelectTrigger className="w-60">
                    <SelectValue placeholder="Select Receipt Type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        {receipts.map((receipt) => (
                            <SelectItem key={receipt.value} value={receipt.value}>
                                {receipt.label}
                            </SelectItem>
                        ))}
                    </SelectGroup>
                </SelectContent>
            </Select>
        </Field>
    );
};
