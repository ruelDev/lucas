import { Badge } from '@/components/ui/badge';

/* eslint-disable react/prop-types */
export function NpaBadge({ stage }) {
    if (!stage) return <span className="text-sm font-medium">—</span>;

    const isWriteoff = stage.toUpperCase() === 'WRITEOFF';

    return (
        <Badge variant="secondary" className={isWriteoff ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : ''}>
            {stage}
        </Badge>
    );
}
