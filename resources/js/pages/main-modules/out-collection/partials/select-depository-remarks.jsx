/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { Check, ChevronsUpDown } from 'lucide-react';
import * as React from 'react';

export default function SelectDepositoryRemarks({ banks, selectedBankId, value, onChange }) {
    const [open, setOpen] = React.useState(false);

    const accounts = banks.find((bank) => String(bank.id) === String(selectedBankId))?.bank_accounts ?? [];

    const selectedAccount = accounts.find((a) => String(a.id) === String(value));

    const isFirstRender = React.useRef(true);

    React.useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }
        onChange('');
    }, [selectedBankId]);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    role="combobox"
                    aria-expanded={open}
                    className="w-full justify-between font-normal"
                    disabled={!selectedBankId}
                >
                    {selectedAccount?.depository_remarks || (selectedBankId ? 'Select account...' : 'Select a bank first')}
                    <ChevronsUpDown className="h-4 w-4 opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-60 p-0">
                <Command>
                    <CommandInput placeholder="Search accounts..." className="h-9" />
                    <CommandList>
                        <CommandEmpty>No accounts found.</CommandEmpty>
                        <CommandGroup>
                            {accounts.map((account) => (
                                <CommandItem
                                    key={account.id}
                                    value={String(account.id)}
                                    onSelect={() => {
                                        onChange(String(account.id) === String(value) ? '' : String(account.id));
                                        setOpen(false);
                                    }}
                                >
                                    {account.depository_remarks}
                                    <Check className={cn('ml-auto h-4 w-4', String(value) === String(account.id) ? 'opacity-100' : 'opacity-0')} />
                                </CommandItem>
                            ))}
                        </CommandGroup>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
