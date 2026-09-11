/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useUrlParams } from '@/hooks/use-url-params';
import axios from 'axios';
import { ChevronDownIcon, Filter, Search } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { Calendar } from './ui/calendar';
import { Label } from './ui/label';
import { Popover, PopoverContent, PopoverTrigger } from './ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from './ui/select';

export const DataTableControls = ({
    filters,
    searchTerm,
    onSearchChange,
    onExportAction,
    exportProps = {},
    onCreateAction,
    showSearchBar = false,
    showDateFilters = false,
    dateFrom = '',
    dateTo = '',
    onDateFromChange,
    onDateToChange,
    onClearDateFilters,
    dateFromLabel = 'Date From',
    dateToLabel = 'Date To',
    onDepositAction,
    rp_searchFilter = false,
    userReportsControls = false,
}) => {
    // for receipt posting functionalities
    const [rpSearchFilter, setRpSearchFilter] = useState('');
    const [rpSearchDateOpen, setRpSearchDateOpen] = useState(false);
    const [rpSearchDate, setRpSearchDate] = useState('');

    const [auditLogDateFromOpen, setAuditLogDateFromOpen] = useState(false);
    const [auditLogDateFrom, setAuditLogDateFrom] = useState('');

    const [auditLogDateToOpen, setAuditLogDateToOpen] = useState(false);
    const [auditLogDateTo, setAuditLogDateTo] = useState('');

    const [roleOptions, setRoleOptions] = useState([]);

    useEffect(() => {
        if (!userReportsControls) return;

        axios
            .get(route('getRoleOptions'))
            .then((res) => {
                setRoleOptions(res.data);
            })
            .catch((err) => {
                console.error('Failed to fetch roles:', err);
            });
    }, [userReportsControls]);

    const { makeRequest } = useUrlParams();

    const formatDate = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    return (
        <div className="mb-4 flex items-center justify-between">
            {showSearchBar && (
                <div className="flex flex-1 items-center space-x-2">
                    <div className="relative max-w-sm flex-1">
                        <Search className="text-muted-foreground absolute top-2.5 left-2 h-4 w-4" />
                        <Input
                            type="search"
                            placeholder="Search across all columns..."
                            value={searchTerm}
                            onChange={(e) => onSearchChange(e.target.value)}
                            className="pl-8"
                        />
                    </div>
                </div>
            )}
            {rp_searchFilter && (
                <div className="flex items-center space-x-4">
                    <div className="flex items-center space-x-2">
                        <Filter className="h-4 w-4 text-gray-500" />
                        <span className="text-sm font-medium text-gray-700">Filter:</span>
                    </div>
                    <div className="relative">
                        <Select value={String(rpSearchFilter)} onValueChange={(value) => setRpSearchFilter(value)}>
                            <SelectTrigger className="xl:w-[200px]">
                                <SelectValue placeholder="Filter By" />
                            </SelectTrigger>
                            <SelectContent className="xl:w-[200px]">
                                <SelectGroup>
                                    <SelectItem value="MAKER_ID">Maker ID</SelectItem>
                                    <SelectItem value="DEPOSIT_DATE">Deposit Date</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                    {rpSearchFilter === 'MAKER_ID' && (
                        <div className="relative flex-1">
                            <Search className="text-muted-foreground absolute top-2.5 left-2 h-4 w-4" />
                            <Input
                                type="search"
                                placeholder="Search across all columns..."
                                value={searchTerm}
                                onChange={(e) => onSearchChange(e.target.value)}
                                className="pl-8"
                            />
                        </div>
                    )}
                    {rpSearchFilter === 'DEPOSIT_DATE' && (
                        <div className="relative flex-1">
                            <Popover open={rpSearchDateOpen} onOpenChange={setRpSearchDateOpen}>
                                <PopoverTrigger asChild>
                                    <Button variant="outline" id="rpSearchDate" className="justify-between font-normal">
                                        {rpSearchDate ? rpSearchDate.toLocaleDateString() : 'Select Date'}
                                        <ChevronDownIcon />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className="w-auto overflow-hidden p-0" align="center">
                                    <Calendar
                                        className="data-[selected=true]:text-blue-600 xl:w-[250px]"
                                        mode="single"
                                        selected={searchTerm}
                                        captionLayout="dropdown"
                                        onSelect={(date) => {
                                            setRpSearchDate(date);
                                            onSearchChange(formatDate(date));
                                            setRpSearchDateOpen(false);
                                        }}
                                    />
                                </PopoverContent>
                            </Popover>
                        </div>
                    )}
                </div>
            )}

            {userReportsControls && (
                <div className="flex items-center space-x-2">
                    <Select
                        value={filters?.role || 'all'}
                        onValueChange={(value) =>
                            makeRequest({
                                ...filters,
                                role: value === 'all' ? '' : value,
                                page: 1,
                            })
                        }
                    >
                        <SelectTrigger className="w-[180px]">
                            <SelectValue placeholder="All Roles" />
                        </SelectTrigger>

                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="all">All Roles</SelectItem>

                                {roleOptions.map((role) => (
                                    <SelectItem key={role.name} value={role.name}>
                                        {role.name}
                                    </SelectItem>
                                ))}
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
            )}

            <div className="mb-4 flex items-center justify-between">
                {onExportAction && React.cloneElement(onExportAction, exportProps)}

                {onCreateAction && onCreateAction}

                {onDepositAction && onDepositAction}

                {showDateFilters && (
                    <>
                        <div className="relative mx-3 flex items-center space-x-2">
                            <Label className="min-w-fit text-sm text-gray-600">{dateFromLabel}:</Label>
                            <Popover open={auditLogDateFromOpen} onOpenChange={setAuditLogDateFromOpen}>
                                <PopoverTrigger asChild>
                                    <Button variant="outline" id="auditLogDateFrom" className="justify-between font-normal">
                                        {auditLogDateFrom ? auditLogDateFrom.toLocaleDateString() : 'Select Date'}
                                        <ChevronDownIcon />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className="w-auto overflow-hidden p-0" align="center">
                                    <Calendar
                                        className="data-[selected=true]:text-blue-600 xl:w-[250px]"
                                        mode="single"
                                        selected={dateFrom}
                                        captionLayout="dropdown"
                                        onSelect={(date) => {
                                            setAuditLogDateFrom(date);
                                            onDateFromChange(formatDate(date));
                                            setAuditLogDateFromOpen(false);
                                        }}
                                    />
                                </PopoverContent>
                            </Popover>
                        </div>

                        <div className="relative mx-3 flex items-center space-x-2">
                            <Label className="min-w-fit text-sm text-gray-600">{dateToLabel}:</Label>
                            <Popover open={auditLogDateToOpen} onOpenChange={setAuditLogDateToOpen}>
                                <PopoverTrigger asChild>
                                    <Button variant="outline" id="auditLogDateTo" className="justify-between font-normal">
                                        {auditLogDateTo ? auditLogDateTo.toLocaleDateString() : 'Select Date'}
                                        <ChevronDownIcon />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className="w-auto overflow-hidden p-0" align="center">
                                    <Calendar
                                        className="data-[selected=true]:text-blue-600 xl:w-[250px]"
                                        mode="single"
                                        selected={dateTo}
                                        captionLayout="dropdown"
                                        onSelect={(date) => {
                                            setAuditLogDateTo(date);
                                            onDateToChange(formatDate(date));
                                            setAuditLogDateToOpen(false);
                                        }}
                                    />
                                </PopoverContent>
                            </Popover>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};
