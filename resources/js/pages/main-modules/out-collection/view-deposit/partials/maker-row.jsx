/* eslint-disable react/prop-types */
export function MakerRow({ name, makerId }) {
    const initials = name
        ? name
              .split(' ')
              .map((w) => w[0])
              .join('')
              .slice(0, 2)
              .toUpperCase()
        : '??';

    return (
        <div className="border-border/40 mb-4 flex items-center gap-3 border-b pb-4">
            <div className="bg-primary/10 text-primary flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-medium">
                {initials}
            </div>
            <div className="min-w-0">
                <p className="text-sm font-medium">{name}</p>
                <p className="text-muted-foreground text-xs">Maker ID: {makerId}</p>
            </div>
        </div>
    );
}
