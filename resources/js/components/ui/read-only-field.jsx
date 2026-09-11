import { Label } from "./label";


export default function ReadOnlyField({ label, value, rightSlot = null }) {
    return (
        <div className='space-y-2'>
            <Label>{label}</Label>
            <div className="flex items-center justify-between rounded-md border bg-muted px-3 py-2 text-sm">
                <span>{value ?? "—"}</span>
                {rightSlot}
            </div>
        </div>
    )
}