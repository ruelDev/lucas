import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';

/* eslint-disable react/prop-types */
export default function SelectBank({ banks, onSelectedBank, selectedBankId }) {
    return (
        <Select name="bank" onValueChange={(value) => onSelectedBank(value)} value={selectedBankId}>
            <SelectTrigger className="w-full">
                <SelectValue placeholder="Select a bank" />
            </SelectTrigger>
            <SelectContent className="max-h-60">
                <SelectGroup>
                    <SelectLabel>Banks</SelectLabel>
                    {banks.map((bank) => (
                        <SelectItem key={bank.id} value={String(bank.id)}>
                            {bank.name}
                        </SelectItem>
                    ))}
                </SelectGroup>
            </SelectContent>
        </Select>
    );
}
