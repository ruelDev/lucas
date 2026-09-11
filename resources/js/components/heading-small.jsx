export default function HeadingSmall({ title, description }) {
    return (
        <header>
            <h3 className="mb-0.5 text-base font-bold">{title}</h3>
            {description && <p className="text-muted-foreground text-sm">{description}</p>}
        </header>
    );
}
