/* eslint-disable react/prop-types */
'use client';

import { TrendingUp } from 'lucide-react';
import { Bar, BarChart, CartesianGrid, LabelList, XAxis, YAxis } from 'recharts';

import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart';

export default function RoleUsers({ roles }) {
    const chartData = roles;

    const sortedData = [...chartData].sort((a, b) => b.users - a.users);

    const chartConfig = {
        users: {
            label: 'Users',
            color: '#1B4298',
        },
    };

    const getHighestUserCount = () => {
        return sortedData[0] ?? null;
    };

    const highest = getHighestUserCount();

    return (
        <Card className="border-brand-primary border-l-4 shadow-sm transition-shadow hover:shadow-md">
            {/* Header */}
            <CardHeader>
                <CardTitle className="text-brand-primary">Users per Role</CardTitle>

                <CardDescription className="text-brand-primary/70">Distribution of users across roles</CardDescription>
            </CardHeader>

            {/* Chart */}
            <CardContent>
                <ChartContainer config={chartConfig}>
                    <BarChart layout="vertical" data={sortedData} margin={{ left: 20 }}>
                        <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />

                        {/* Users scale */}
                        <XAxis type="number" hide />

                        {/* Role labels */}
                        <YAxis type="category" dataKey="name" width={160} tickLine={false} axisLine={false} className="text-xs" />

                        <ChartTooltip cursor={{ fill: 'rgba(27,66,152,0.05)' }} content={<ChartTooltipContent />} />

                        <Bar dataKey="users" fill="#1B4298" radius={6}>
                            <LabelList dataKey="users" position="right" className="fill-brand-primary font-semibold" fontSize={12} />
                        </Bar>
                    </BarChart>
                </ChartContainer>
            </CardContent>

            {/* Footer */}
            <CardFooter className="flex flex-col items-start gap-2 text-sm">
                <div className="text-brand-accent flex gap-2 leading-none font-medium">
                    {highest && `Highest: ${highest.name} (${highest.users} users)`}
                    <TrendingUp className="h-4 w-4" />
                </div>

                <div className="text-muted-foreground leading-none">Role-based user distribution</div>
            </CardFooter>
        </Card>
    );
}
