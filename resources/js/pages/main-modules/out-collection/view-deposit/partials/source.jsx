import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

/* eslint-disable react/prop-types */
export function Source({ value, onValueChange }) {
    const lists = [
        { value: 'newgen', label: 'NewGen' },
        { value: 'finnone', label: 'Finnone' },
    ];

    return (
        <Select value={value} onValueChange={onValueChange}>
            <SelectTrigger>
                <SelectValue placeholder="Select a source" />
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
