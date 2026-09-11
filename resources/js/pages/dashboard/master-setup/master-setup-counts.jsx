/* eslint-disable react/prop-types */
import { Card } from '@/components/ui/card';

function getInitial(label) {
    return label.charAt(0);
}

export default function MasterSetupCounts({ masterSetupData }) {
    const masterData = [
        { label: 'Branches', count: masterSetupData.totalBranches, color: 'bg-blue-500/10 text-blue-700' },
        { label: 'Dealers', count: masterSetupData.totalDealers, color: 'bg-indigo-500/10 text-indigo-700' },
        { label: 'Groups', count: masterSetupData.totalGroups, color: 'bg-purple-500/10 text-purple-700' },
        { label: 'Divisions', count: masterSetupData.totalDivisions, color: 'bg-cyan-500/10 text-cyan-700' },
        { label: 'Departments', count: masterSetupData.totalDepartments, color: 'bg-emerald-500/10 text-emerald-700' },
        { label: 'Sections', count: masterSetupData.totalSections, color: 'bg-orange-500/10 text-orange-700' },
    ];

    return (
        <div className="flex h-full w-full flex-col gap-4">
            <Card className="border-brand-primary border-l-4 px-5 py-4 shadow-sm">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-brand-primary/70 text-sm">System Configuration</p>
                        <h2 className="text-brand-primary text-2xl font-bold tracking-tight">Master Setup</h2>
                    </div>

                    <div className="text-muted-foreground text-right text-xs">
                        <div>{masterData.length} Categories</div>
                        <div>Total Entities</div>
                    </div>
                </div>
            </Card>

            <div className="grid h-full w-full grid-cols-2 gap-5">
                {masterData.map((item) => (
                    <Card
                        key={item.label}
                        className="group border-border/60 from-background to-muted/30 relative overflow-hidden rounded-xl border bg-gradient-to-br p-5 shadow-[0_2px_6px_rgba(0,0,0,0.06)] transition-all duration-200 hover:-translate-y-[3px] hover:shadow-[0_8px_20px_rgba(0,0,0,0.12)]"
                    >
                        {/* Accent bar */}
                        <div className="bg-brand-primary absolute top-0 left-0 h-full w-[3px] opacity-80 transition-opacity group-hover:opacity-100" />

                        {/* Glow */}
                        <div className="pointer-events-none absolute inset-0 rounded-xl ring-1 ring-white/30" />

                        <div className="flex items-center justify-between">
                            <div className="space-y-1">
                                <p className="text-muted-foreground text-sm">{item.label}</p>
                                <p className="text-foreground text-3xl font-bold tracking-tight tabular-nums">{item.count}</p>
                            </div>

                            <div className={`flex h-11 w-11 items-center justify-center rounded-lg text-sm font-semibold shadow-inner ${item.color}`}>
                                {getInitial(item.label)}
                            </div>
                        </div>
                    </Card>
                ))}
            </div>
        </div>
    );
}
