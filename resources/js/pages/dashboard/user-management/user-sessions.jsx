/* eslint-disable react/prop-types */
'use client';

import * as React from 'react';
import { Area, AreaChart, CartesianGrid, XAxis } from 'recharts';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart';

import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export const description = 'User login activity chart';

const chartConfig = {
    logins: {
        label: 'User Logins',
        color: '#1B4298',
    },
};

function fillMissingDates(data) {
    if (!data || data.length === 0) return [];

    const sorted = [...data].sort((a, b) => new Date(a.date) - new Date(b.date));

    const startDate = new Date(sorted[0].date);
    const endDate = new Date(sorted.at(-1).date);

    const map = new Map(data.map((item) => [item.date, item.logins]));

    const result = [];
    let current = new Date(startDate);

    while (current <= endDate) {
        const formatted = current.toISOString().split('T')[0];

        result.push({
            date: formatted,
            logins: map.get(formatted) ?? 0,
        });

        current = new Date(current)
        current.setDate(current.getDate() + 1);
    }

    return result;
}

export default function UserSessions({ userData }) {
    const chartData = userData.sessionCounts;

    const [timeRange, setTimeRange] = React.useState('30d');

    const referenceDate = new Date('2024-05-26');

    const normalizedData = fillMissingDates(chartData);

    const filteredData = normalizedData.filter((item) => {
        const date = new Date(item.date);

        let daysToSubtract = 30;
        if (timeRange === '7d') daysToSubtract = 7;
        if (timeRange === '90d') daysToSubtract = 90;

        const startDate = new Date(referenceDate);
        startDate.setDate(startDate.getDate() - daysToSubtract);

        return date >= startDate;
    });

    return (
        <Card className="pt-0">
            <CardHeader className="flex items-center gap-2 border-b py-5 sm:flex-row">
                <div className="grid flex-1 gap-1">
                    <CardTitle className="text-brand-primary">User Login Activity</CardTitle>
                    <CardDescription className="text-brand-primary/70">Number of users logging in per day</CardDescription>
                </div>

                <Select value={timeRange} onValueChange={setTimeRange}>
                    <SelectTrigger className="hidden w-[160px] sm:ml-auto sm:flex">
                        <SelectValue placeholder="Select range" />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectItem value="90d">Last 90 days</SelectItem>
                        <SelectItem value="30d">Last 30 days</SelectItem>
                        <SelectItem value="7d">Last 7 days</SelectItem>
                    </SelectContent>
                </Select>
            </CardHeader>

            <CardContent className="px-2 pt-4 sm:px-6 sm:pt-6">
                <ChartContainer config={chartConfig} className="h-[250px] w-full">
                    <AreaChart data={filteredData} margin={{ left: 12, right: 12 }}>
                        <CartesianGrid vertical={false} />

                        <XAxis
                            dataKey="date"
                            tickLine={false}
                            axisLine={false}
                            tickMargin={8}
                            minTickGap={32}
                            tickFormatter={(value) =>
                                new Date(value).toLocaleDateString('en-US', {
                                    month: 'short',
                                    day: 'numeric',
                                })
                            }
                        />

                        <ChartTooltip
                            cursor={false}
                            content={
                                <ChartTooltipContent
                                    labelFormatter={(value) =>
                                        new Date(value).toLocaleDateString('en-US', {
                                            month: 'short',
                                            day: 'numeric',
                                        })
                                    }
                                    indicator="line"
                                />
                            }
                        />

                        <Area dataKey="logins" type="natural" fill="var(--color-logins)" fillOpacity={0.4} stroke="var(--color-logins)" />
                    </AreaChart>
                </ChartContainer>
            </CardContent>
        </Card>
    );
}
