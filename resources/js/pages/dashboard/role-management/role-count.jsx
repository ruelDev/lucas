/* eslint-disable react/prop-types */
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
}

function getPermissionVariant(count) {
    if (count >= 12) return 'bg-blue-900 text-white';
    if (count >= 8) return 'bg-blue-800 text-white';
    return 'bg-blue-700 text-white';
}

export default function RoleCounts({ roles }) {
    const total = roles.length;

    const totalPermissions = roles.reduce((sum, r) => sum + r.permissions, 0);
    const avgPermissions = Math.round(totalPermissions / total);

    return (
        <Card className="border-brand-primary flex h-full flex-col border-l-4 shadow-sm transition-shadow hover:shadow-md">
            {/* Header */}
            <CardHeader className="space-y-2 py-4">
                <CardDescription className="text-brand-primary/70">Role Structure</CardDescription>

                <div className="flex items-end justify-between">
                    <CardTitle className="text-brand-primary text-4xl font-bold tracking-tight">{total}</CardTitle>

                    {/* Micro Insight */}
                    <div className="text-muted-foreground text-right text-xs">
                        <div>{totalPermissions} Total Permissions</div>
                        <div>{avgPermissions} Avg / Role</div>
                    </div>
                </div>
            </CardHeader>

            {/* Table */}
            <CardContent className="flex-1 pt-2">
                <CardDescription className="text-md">Recently Created Roles</CardDescription>
                <div className="bg-background max-h-[655px] overflow-y-auto rounded-md border">
                    <Table>
                        {/* Sticky Header */}
                        <TableHeader className="bg-background sticky top-0 z-10">
                            <TableRow>
                                <TableHead className="text-brand-primary">Role</TableHead>
                                <TableHead className="text-brand-primary">Created</TableHead>
                                <TableHead className="text-brand-primary text-right">Permissions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {roles.map((role) => (
                                <TableRow key={role.name} className="hover:bg-brand-primary/5 cursor-pointer transition-colors">
                                    <TableCell>
                                        <div className="flex items-center gap-3">
                                            <Tooltip>
                                                <TooltipTrigger>
                                                    <div className="text-brand-primary w-12 truncate font-medium">{role.name}</div>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>{role.name}</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </div>
                                    </TableCell>

                                    <TableCell className="text-muted-foreground text-sm">{formatDate(role.created_at)}</TableCell>

                                    <TableCell className="text-right">
                                        <Badge className={getPermissionVariant(role.permissions)}>
                                            <span className="font-semibold">{role.permissions}</span>
                                            <span className="ml-1 text-xs opacity-80">perms</span>
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    );
}
