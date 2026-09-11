/* eslint-disable react/prop-types */
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const formatTimestamp = (date) =>
    new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });


export default function CertificateOfFullPaymentCounts({ certificateOfFullPaymentData }) {
     const safeData = certificateOfFullPaymentData ?? {};
     const totalTopUserCFPCount = safeData.totalTopUserCFPCount ?? [];
     const totalTopCFPGeneratedCount = safeData.totalTopCFPGeneratedCount ?? [];
     const totalListCFPGeneratedCount = safeData.totalListCFPGeneratedCount ?? [];

    const total = totalTopCFPGeneratedCount.reduce((sum, s) => sum + (s.count || 0), 0);    
    
    return (
        <div className="flex w-full flex-col gap-4 lg:flex-row">
            {/* LEFT: KPI + RECENT LOGS */}
            <div className="lg:w-2/3">
                <Card className="border-brand-primary flex h-full flex-col border-l-4 shadow-sm transition-all hover:shadow-md">
                    {/* KPI */}
                    <CardHeader className="pb-2">
                        <CardDescription className="text-brand-primary/70">Total CFP Generated</CardDescription>

                        <CardTitle className="text-brand-primary text-4xl font-bold tabular-nums">{total.toLocaleString()}</CardTitle>
                    </CardHeader>

                    {/* Logs Table */}
                    <CardContent className="flex-1 pt-2">
                        <div className="max-h-[260px] overflow-y-auto rounded-md border">
                            <Table>
                                <TableHeader className="bg-background sticky top-0 z-10">
                                    <TableRow>
                                        <TableHead>Agreement Number</TableHead>
                                        <TableHead>User</TableHead>
                                        <TableHead className="text-right">Timestamp</TableHead>
                                    </TableRow>
                                </TableHeader>

                                <TableBody>
                                    {totalListCFPGeneratedCount.map((log) => (
                                        <TableRow key={log.agreement} className="hover:bg-muted/50 transition-colors">
                                            {/* Agreement */}
                                            <TableCell className="text-brand-primary font-medium">{log.agreement}</TableCell>

                                            {/* User */}
                                            <TableCell>
                                                <div className="flex items-center gap-3">
                                                    <span className="font-medium">{log.name}</span>
                                                </div>
                                            </TableCell>

                                            {/* Timestamp */}
                                            <TableCell className="text-muted-foreground text-right">{formatTimestamp(log.last_activity)}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* RIGHT: TOP USERS */}
            <div className="lg:w-1/3">
                <Card className="border-brand-primary flex h-full flex-col border-l-4 shadow-sm transition-all hover:shadow-md">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-brand-primary">Top Authored Users</CardTitle>

                        <CardDescription className="text-brand-primary/70 text-xs">Based on number of CFP reports generated</CardDescription>
                    </CardHeader>

                    <CardContent className="flex-1 pt-2">
                        <div className="max-h-[260px] overflow-y-auto rounded-md border">
                            <Table>
                                <TableHeader className="bg-background sticky top-0 z-10">
                                    <TableRow>
                                        <TableHead>User</TableHead>
                                        <TableHead className="text-right">CFP Authored</TableHead>
                                    </TableRow>
                                </TableHeader>

                               <TableBody>
                                    {totalTopUserCFPCount.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={2} className="text-muted-foreground text-center text-sm">
                                                No user activity yet
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        totalTopUserCFPCount
                                            .sort((a, b) => (b.count || 0) - (a.count || 0))
                                            .map((user, index) => (
                                                <TableRow key={user?.name || index}>
                                                    <TableCell>
                                                        <div className="flex items-center gap-3">
                                                            <div className="text-muted-foreground w-4 text-xs">#{index + 1}</div>
                                                            <span className="text-brand-primary font-medium">{user?.name || 'Unknown'}</span>
                                                        </div>
                                                    </TableCell>
    
                                                    <TableCell className="text-right font-semibold tabular-nums">
                                                        {(user?.count || 0).toLocaleString()}
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
