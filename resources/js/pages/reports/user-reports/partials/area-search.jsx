/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import axios from 'axios';
import { CheckIcon, ChevronsUpDownIcon } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function AreaSearch({ areaSearch, onAreaSearchChange }) {
    const [areaOptions, setAreaOptions] = useState([]);
    const [cbAreaOpen, setCbAreaOpen] = useState(false);

    useEffect(() => {
        const fetchAreaData = async () => {
            try {
                const res = await axios.get('/get-area-options');
                setAreaOptions(res.data.data);
            } catch (err) {
                console.error('Error loading area data:', err);
            }
        };

        fetchAreaData();
    }, [areaSearch]);

    return (
        <div className="relative mx-2 flex flex-col items-start space-x-2">
            <Label className="min-w-fit text-sm text-gray-600">Office:</Label>

            <Popover open={cbAreaOpen} onOpenChange={setCbAreaOpen}>
                <PopoverTrigger asChild>
                    <Button variant="outline" className="w-[250px] justify-between">
                        {areaSearch ? areaOptions.find((org) => org.name === areaSearch)?.name : 'Select Office...'}
                        <ChevronsUpDownIcon className="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                </PopoverTrigger>

                <PopoverContent className="w-100">
                    <Command>
                        <CommandInput placeholder="Search Office..." />
                        <CommandList>
                            <CommandEmpty>No office found</CommandEmpty>

                            <CommandGroup>
                                {areaOptions.map((org) => (
                                    <CommandItem
                                        key={org.name}
                                        value={org.name}
                                        onSelect={(value) => {
                                            const newValue = value === areaSearch ? '' : value;

                                            onAreaSearchChange(newValue);
                                            setCbAreaOpen(false);
                                        }}
                                    >
                                        <CheckIcon className={`mr-2 h-4 w-4 ${areaSearch === org.name ? 'opacity-100' : 'opacity-0'}`} />
                                        {org.name}
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
