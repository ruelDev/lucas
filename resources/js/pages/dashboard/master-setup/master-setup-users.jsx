/* eslint-disable react/prop-types */
'use client';

import { TrendingUp } from 'lucide-react';
import { Cell, Label, Pie, PieChart } from 'recharts';

import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';

import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent } from '@/components/ui/chart';
import { useCallback } from 'react';

const chartConfig = {
    Branches: { label: 'Branches', color: '#1B4298' },
    'Dealer Branches': { label: 'Dealer Branches', color: '#3A66C1' },
    'Head Offices': { label: 'Head Offices', color: '#F26531' },
};

const CenterLabel = ({ viewBox, total }) => {
    if (!viewBox) return null;

    const { cx, cy } = viewBox;

    return (
        <text x={cx} y={cy} textAnchor="middle" dominantBaseline="middle">
            <tspan x={cx} y={cy - 6} className="fill-brand-primary text-3xl font-bold">
                {total}
            </tspan>

            <tspan x={cx} y={cy + 18} className="fill-muted-foreground text-xs tracking-wide">
                TOTAL USERS
            </tspan>
        </text>
    );
};

export default function MasterSetupUsers({ masterSetupData }) {
    const chartData = [
        { name: 'Branches', value: masterSetupData.branchUsers, fill: '#1B4298' },
        { name: 'Dealer Branches', value: masterSetupData.dealerUsers, fill: '#3A66C1' },
        { name: 'Head Offices', value: masterSetupData.headOfficeUsers, fill: '#F26531' },
    ];

    const total = chartData.reduce((sum, item) => sum + item.value, 0);
    const maxValue = Math.max(...chartData.map((d) => d.value));

    const renderCenterLabel = useCallback(
        (props) => <CenterLabel {...props} total={total} />,
        [total]
    );

    return (
        <Card className="border-border/60 from-background to-muted/30 border bg-gradient-to-br shadow-sm transition-all hover:shadow-md">
            {/* Header */}
            <CardHeader className="items-center pb-2">
                <CardTitle className="text-brand-primary">User Distribution</CardTitle>

                <CardDescription className="text-brand-primary/70">Across organizational levels</CardDescription>
            </CardHeader>

            {/* Chart */}
            <CardContent className="flex flex-1 items-center justify-center pb-2">
                <div className="relative h-[320px] w-full max-w-[420px]">
                    {/* Soft glow background */}
                    <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <div className="bg-muted/30 h-[220px] w-[220px] rounded-full opacity-40 blur-2xl" />
                    </div>

                    <ChartContainer config={chartConfig} className="h-full w-full">
                        <PieChart>
                            {/* Tooltip */}
                            <ChartTooltip cursor={{ fill: 'rgba(27,66,152,0.05)' }} content={<ChartTooltipContent />} />

                            {/* Donut */}
                            <Pie
                                data={chartData}
                                dataKey="value"
                                nameKey="name"
                                innerRadius={85}
                                outerRadius={115}
                                stroke="white"
                                strokeWidth={2}
                                activeOuterRadius={125}
                                isAnimationActive
                                animationDuration={500}
                                animationEasing="ease-out"
                            >
                                {chartData.map((entry) => (
                                    <Cell
                                        key={entry.name}
                                        fill={entry.fill}
                                        style={{
                                            filter: entry.value === maxValue ? 'drop-shadow(0 6px 12px rgba(0,0,0,0.15))' : 'none',
                                        }}
                                    />
                                ))}

                                {/* Center Label */}
                                <Label 
                                    position="center" 
                                    content={renderCenterLabel}
                                />
                            </Pie>

                            {/* Legend */}
                            <ChartLegend content={<ChartLegendContent />} />
                        </PieChart>
                    </ChartContainer>
                </div>
            </CardContent>

            {/* Footer */}
            <CardFooter className="flex flex-col gap-2 text-sm">
                <div className="text-brand-accent flex items-center gap-2 font-medium">
                    Majority in Head Offices
                    <TrendingUp className="h-4 w-4" />
                </div>

                <div className="text-muted-foreground">Distribution across system structure</div>
            </CardFooter>
        </Card>
    );
}
