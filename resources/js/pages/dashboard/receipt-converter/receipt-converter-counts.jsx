/* eslint-disable react/prop-types */
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const getInitials = (text) => {
    if (!text || typeof text !== 'string') return '?';
    return text.charAt(0).toUpperCase();
};

const getFileType = (filename) => {
    if (!filename || typeof filename !== 'string') return '?';
    const parts = filename.split('.');
    return parts.length > 1 ? parts.pop().toUpperCase() : 'FILE';
};

const formatTimestamp = (date) => {
    if (!date) return '—';
    const parsed = new Date(date);
    if (Number.isNaN(parsed)) return 'Invalid date';

    return parsed.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

export default function ReceiptConverterCounts({ receiptConverterData }) {
    const safeData = receiptConverterData ?? {};

    const sourceData = [
        { source: 'NEWGEN QR/UA', count: safeData.totalNewgenQrUaCount || 0 },
        { source: 'FINNONE UA', count: safeData.totalFinnoneUaCount || 0 },
        { source: 'FINNONE QR', count: safeData.totalFinnoneQrCount || 0 },
    ];

    const recentlyGeneratedFiles = safeData.recentlyGeneratedFiles ?? [];
    const authoredUserGenerations = safeData.authoredUserGenerations ?? [];

    const hasSourceData = sourceData.some((s) => s.count > 0);
    const total = sourceData.reduce((sum, s) => sum + (s.count || 0), 0);

    return (
        <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <Card className="border-brand-primary flex flex-col border-l-4 shadow-sm transition-all hover:shadow-md">
                <CardHeader className="pb-2">
                    <CardDescription className="text-brand-primary/70">Total Receipt Conversions</CardDescription>

                    <CardTitle className="text-brand-primary text-4xl font-bold tabular-nums">{total.toLocaleString()}</CardTitle>
                </CardHeader>

                <CardContent className="pt-2">
                    <div className="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Source</TableHead>
                                    <TableHead className="text-right">Generated</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                {hasSourceData ? (
                                    sourceData
                                        .toSorted((a, b) => (b.count || 0) - (a.count || 0))
                                        .map((item) => (
                                            <TableRow key={item.source}>
                                                <TableCell className="text-brand-primary font-medium">{item.source || '—'}</TableCell>
                                                <TableCell className="text-right font-semibold tabular-nums">
                                                    {(item.count || 0).toLocaleString()}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                ) : (
                                    <TableRow>
                                        <TableCell colSpan={2} className="text-muted-foreground text-center text-sm">
                                            No data available
                                        </TableCell>
                                    </TableRow>
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <Card className="border-brand-primary flex flex-col border-l-4 shadow-sm transition-all hover:shadow-md">
                <CardHeader className="pb-2">
                    <CardTitle className="text-brand-primary">Generated Files</CardTitle>

                    <CardDescription className="text-brand-primary/70 text-xs">Latest converted receipts by source</CardDescription>
                </CardHeader>

                <CardContent className="flex-1 pt-2">
                    <div className="max-h-[260px] overflow-y-auto rounded-md border">
                        <Table>
                            <TableHeader className="bg-background sticky top-0 z-10">
                                <TableRow>
                                    <TableHead>Source</TableHead>
                                    <TableHead>Filename</TableHead>
                                    <TableHead className="text-right">Generated</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                {recentlyGeneratedFiles.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={3} className="text-muted-foreground text-center text-sm">
                                            No recent files
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    recentlyGeneratedFiles.map((log, index) => (
                                        <TableRow key={log?.filename || index}>
                                            <TableCell className="text-brand-primary font-medium">{log?.source || '—'}</TableCell>

                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <div className="bg-brand-primary/10 text-brand-primary flex h-7 w-7 items-center justify-center rounded-md text-xs font-semibold">
                                                        {getFileType(log?.filename)}
                                                    </div>
                                                    <span className="font-medium">{log?.filename || 'Unknown file'}</span>
                                                </div>
                                            </TableCell>

                                            <TableCell className="text-muted-foreground text-right text-xs">
                                                {formatTimestamp(log?.timestamp)}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <Card className="border-brand-primary flex flex-col border-l-4 shadow-sm transition-all hover:shadow-md">
                <CardHeader className="pb-2">
                    <CardTitle className="text-brand-primary">Top Authored Users</CardTitle>

                    <CardDescription className="text-brand-primary/70 text-xs">Based on receipt conversions</CardDescription>
                </CardHeader>

                <CardContent className="flex-1 pt-2">
                    <div className="max-h-[260px] overflow-y-auto rounded-md border">
                        <Table>
                            <TableHeader className="bg-background sticky top-0 z-10">
                                <TableRow>
                                    <TableHead>User</TableHead>
                                    <TableHead className="text-right">Generated</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                {authoredUserGenerations.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={2} className="text-muted-foreground text-center text-sm">
                                            No user activity yet
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    authoredUserGenerations
                                        .sort((a, b) => (b.count || 0) - (a.count || 0))
                                        .map((user, index) => (
                                            <TableRow key={user?.name || index}>
                                                <TableCell>
                                                    <div className="flex items-center gap-3">
                                                        <div className="text-muted-foreground w-4 text-xs">#{index + 1}</div>

                                                        <div className="bg-brand-primary/10 text-brand-primary flex h-8 w-8 items-center justify-center rounded-md text-sm font-semibold">
                                                            {getInitials(user?.name)}
                                                        </div>

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
    );
}
