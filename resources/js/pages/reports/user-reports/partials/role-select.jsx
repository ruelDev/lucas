/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import axios from 'axios';
import { CheckIcon, ChevronsUpDownIcon } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function RoleSelect({ roleSelect, onRoleSelectChange }) {
    const [roleCbOpen, setRoleCbOpen] = useState(false);
    const [roleOptions, setRoleOptions] = useState([]);

    useEffect(() => {
        axios
            .get(route('getRoleOptions'))
            .then((res) => {
                setRoleOptions(res.data);
            })
            .catch((err) => {
                console.error('Failed to fetch roles:', err);
            });
    }, [roleSelect]);

    return (
        <div className="relative mx-2 flex flex-col items-start space-x-2">
            <Label className="min-w-fit text-sm text-gray-600">Role:</Label>

            <Popover open={roleCbOpen} onOpenChange={setRoleCbOpen}>
                <PopoverTrigger asChild>
                    <Button variant="outline" className="w-[250px] justify-between">
                        {roleSelect ? roleOptions.find((role) => role.name === roleSelect)?.name : 'Select Role...'}
                        <ChevronsUpDownIcon className="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                </PopoverTrigger>

                <PopoverContent className="w-100">
                    <Command>
                        <CommandInput placeholder="Search Role..." />
                        <CommandList>
                            <CommandEmpty>No role found</CommandEmpty>

                            <CommandGroup>
                                {roleOptions.map((role) => (
                                    <CommandItem
                                        key={role.name}
                                        value={role.name}
                                        onSelect={(value) => {
                                            const newValue = value === roleSelect ? '' : value;

                                            onRoleSelectChange(newValue);
                                            setRoleCbOpen(false);
                                        }}
                                    >
                                        <CheckIcon className={`mr-2 h-4 w-4 ${roleSelect === role.name ? 'opacity-100' : 'opacity-0'}`} />
                                        {role.name}
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
        </div>
    );
}
