/* eslint-disable react/prop-types */
import {
    CheckCircle2,
    CircleDashed,
    XCircle,
    Clock3,
} from "lucide-react";

const STATUS_CONFIG = {
    Active: {
        icon: CheckCircle2,
        className:
            "bg-emerald-50 text-emerald-700 border border-emerald-200",
    },

    Inactive: {
        icon: CircleDashed,
        className:
            "bg-slate-100 text-slate-600 border border-slate-200",
    },

    Pending: {
        icon: Clock3,
        className:
            "bg-amber-50 text-amber-700 border border-amber-200",
    },

    Suspended: {
        icon: XCircle,
        className:
            "bg-red-50 text-red-700 border border-red-200",
    },
};

export default function StatusBadge({ status }) {
    const config =
        STATUS_CONFIG[status] ??
        STATUS_CONFIG["Inactive"];

    const Icon = config.icon;

    return (
        <span
            className={`inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium ${config.className}`}
        >
            <Icon className="h-3.5 w-3.5" />

            {status}
        </span>
    );
}