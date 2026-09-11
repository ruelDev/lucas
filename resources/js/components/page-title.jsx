/* eslint-disable react/prop-types */
export default function PageTitle({ title, description }) {
    return (
        <div className="px-6 py-5">
            <h2 className="text-4xl font-bold text-blue-500 mb-1">{title}</h2>
            {description && <p className="text-muted-foreground text-sm">{description}</p>}
        </div>
    );
}
