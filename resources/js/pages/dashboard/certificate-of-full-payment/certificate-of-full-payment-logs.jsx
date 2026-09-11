/* eslint-disable react/prop-types */
'use client';

import { Bar, BarChart, CartesianGrid, XAxis } from 'recharts';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

import { ChartContainer, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart';


function fillMissingDates(data) {
    if (!data || data.length === 0) return [];

    const sorted = [...data].sort((a, b) => new Date(a.date) - new Date(b.date));

    const startDate = new Date(sorted[0].date);
    const endDate = new Date(sorted.at(-1).date);

    const map = new Map(data.map((item) => [item.date, item.count]));

    const result = [];
    let current = new Date(startDate);

    while (current <= endDate) {
        const formatted = current.toISOString().split('T')[0];

        result.push({
            date: formatted,
            count: map.get(formatted) ?? 0,
        });

        current = new Date(current)
        current.setDate(current.getDate() + 1);
    }

    return result;
}

const chartConfig = {
    count: {
        label: 'CFP Generated',
        color: '#1B4298',
    },
};

export default function CertificateOfFullPaymentLogs({ certificateOfFullPaymentData }) {
    
    const rawData = certificateOfFullPaymentData.totalGenerationLogs;

    console.log(rawData);
    

    const chartData = fillMissingDates(rawData);

    const total = chartData.reduce((sum, d) => sum + d.count, 0);   

   
    return (
        <Card className="border-border/60 border shadow-sm transition-all hover:shadow-md">
                    {/* Header */}
                    <CardHeader className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle className="text-brand-primary">CFP Generation Trend</CardTitle>
        
                            <CardDescription className="text-brand-primary/70">Daily generated activity</CardDescription>
                        </div>
        
                        {/* Total Summary */}
                        <div className="text-right">
                            <div className="text-muted-foreground text-xs">Total Generate</div>
        
                            <div className="text-brand-primary text-xl font-bold">{total}</div>
                        </div>
                    </CardHeader>
        
                    {/* Chart */}
                    <CardContent>
                        <ChartContainer config={chartConfig} className="h-[280px] w-full">
                            <BarChart data={chartData} margin={{ left: 12, right: 12 }}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
        
                                <XAxis
                                    dataKey="date"
                                    tickLine={false}
                                    axisLine={false}
                                    tickMargin={8}
                                    minTickGap={28}
                                    tickFormatter={(value) => {
                                        const date = new Date(value);
                                        return date.toLocaleDateString('en-US', {
                                            month: 'short',
                                            day: 'numeric',
                                        });
                                    }}
                                />
        
                                <ChartTooltip
                                    content={
                                        <ChartTooltipContent
                                            labelFormatter={(value) =>
                                                new Date(value).toLocaleDateString('en-US', {
                                                    month: 'short',
                                                    day: 'numeric',
                                                    year: 'numeric',
                                                })
                                            }
                                        />
                                    }
                                />
        
                                <Bar dataKey="count" fill="#1B4298" radius={[6, 6, 0, 0]} />
                            </BarChart>
                        </ChartContainer>
                    </CardContent>
                </Card>
    );
}
