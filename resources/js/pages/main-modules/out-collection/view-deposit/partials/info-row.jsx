/* eslint-disable react/prop-types */
export function InfoRow({ label, value }) {
    return (
        <div className="border-border/40 grid grid-cols-[40%_1fr] items-center gap-2 border-b py-2.5 last:border-b-0 last:pb-0">
            <span className="text-muted-foreground text-sm">{label}</span>
            <span className={`text-sm font-medium ${label == 'Source' ? 'uppercase' : ''}`}>{value ?? '-'}</span>
        </div>
    );
}
