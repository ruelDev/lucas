/* eslint-disable react/prop-types */
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export function Reason({ value, onValueChange }) {
    const lists = [
        { value: 'mi', label: 'MI' },
        { value: 'initial cash out', label: 'Initial Cash out' },
        { value: 'penalty', label: 'Penalty' },
        { value: 'processing fee', label: 'Processing Fee' },
        { value: 'notary fee and dst', label: 'Notary Fee and DST' },
        { value: 'closed account', label: 'Closed Account' },
        { value: 'account is in repo status', label: 'Account Is In Repo Status' },
        { value: 'account is in sale status', label: 'Account Is In Sale Status' },
        { value: 'payment for redemption', label: 'Payment For Redemption' },
    ];

    return (
        <Select value={value} onValueChange={onValueChange}>
            <SelectTrigger>
                <SelectValue placeholder="Select a reason" />
            </SelectTrigger>
            <SelectContent>
                <SelectGroup>
                    {lists.map((list) => (
                        <SelectItem key={list.value} value={list.value}>
                            {list.label}
                        </SelectItem>
                    ))}
                </SelectGroup>
            </SelectContent>
        </Select>
    );
}
