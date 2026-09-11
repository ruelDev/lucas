/* eslint-disable react/prop-types */
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export default function StatusSelect({ statusSelect, onStatusChange }) {
    return (
        <div className="relative mx-2 flex flex-col items-start space-y-1">
            <Label className="text-sm text-gray-600">Status:</Label>

            <Select value={statusSelect || ''} onValueChange={onStatusChange}>
                <SelectTrigger className="w-[250px]">
                    <SelectValue placeholder="Select Status..." />
                </SelectTrigger>

                <SelectContent>
                    <SelectItem value="all">All Status</SelectItem>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="inactive">Inactive</SelectItem>
                </SelectContent>
            </Select>
        </div>
    );
}