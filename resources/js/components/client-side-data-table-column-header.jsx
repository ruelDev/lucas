import { cn } from "@/lib/utils";
import { ArrowDown, ArrowUp, ArrowUpDown } from "lucide-react";
// import { Button } from "./ui/button";

export default function DataTableColumnHeader({ column, title, className }) {
    if (!column.getCanSort()) {
        return <div className={cn(className)}>{title}</div>
    }
    return (
        <div className={cn("flex cursor-pointer items-center py-4 text-sm !font-semibold text-zinc-900 dark:text-zinc-100", column.getIsSorted(), className)} onClick={column.getToggleSortingHandler()}>
            {title}
            <span className="ml-2" onClick={column.getToggleSortingHandler()}>
                {column.getIsSorted()
                    ? (column.getIsSorted() === "desc" ? (<ArrowDown className="w-4 h-4" />) : (<ArrowUp className="w-4 h-4" />))
                    : <ArrowUpDown className="w-4 h-4" />}
            </span>
        </div>
    );
}
