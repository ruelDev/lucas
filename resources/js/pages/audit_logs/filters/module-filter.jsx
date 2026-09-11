/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import axios from 'axios';
import { CheckIcon, ChevronsUpDownIcon } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function ModuleSelect({ moduleSelect, onModuleSelectChange }) {
    const [moduleCbOpen, setModuleCbOpen] = useState(false);
    const [moduleOptions, setModuleOptions] = useState([]);

    useEffect(() => {
        axios
            .get(route('getModuleOptions'))
            .then((res) => {
                setModuleOptions(res.data);
            })
            .catch((err) => {
                console.error('Failed to fetch modules: ', err);
            });
    }, [moduleSelect]);

    return (
        <div className="relative flex flex-col items-start space-x-2 space-y-1">
            <Label className="min-w-fit text-sm text-gray-600">Module:</Label>

            <Popover open={moduleCbOpen} onOpenChange={setModuleCbOpen}>
                <PopoverTrigger asChild>
                    <Button variant="outline" className="w-[250px] justify-between">
                        {moduleSelect ? moduleOptions.find((module) => module.name === moduleSelect)?.name : 'Select Module...'}
                        <ChevronsUpDownIcon className="ml-2 h-4 w-4 opacity-50" />
                    </Button>
                </PopoverTrigger>

                <PopoverContent className="w-100">
                    <Command>
                        <CommandInput placeholder="Search Module..." />
                        <CommandList>
                            <CommandEmpty>No module found</CommandEmpty>

                            <CommandGroup>
                                {moduleOptions.map((module) => (
                                    <CommandItem
                                        key={module.name}
                                        value={module.name}
                                        onSelect={(value) => {
                                            const newValue = value === moduleSelect ? '' : value;

                                            onModuleSelectChange(newValue);
                                            setModuleCbOpen(false);
                                        }}
                                    >
                                        <CheckIcon className={`mr-2 h-4 w-4 ${moduleSelect === module.name ? 'opacity-100' : 'opacity-0'}`} />
                                        {module.name}
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
