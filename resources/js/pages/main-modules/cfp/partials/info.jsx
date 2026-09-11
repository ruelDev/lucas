/* eslint-disable react/prop-types */
import { Link } from '@inertiajs/react';
import { Info } from 'lucide-react';

export default function InfoTemp({ customer }) {
    return (
        <Link href={route('cfp.show', customer)} className="flex items-center">
            <Info className="h-4 w-4" />
        </Link>
    );
}


