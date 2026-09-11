/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { can } from '@/lib/can';
import { Download } from 'lucide-react';

export default function ExportBtn({ onExport }) {
    return (
        <>
            {can('user_management.export') && (
                <Button
                    onClick={onExport}
                    variant="outline"
                    className="mx-3 h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                >
                    <Download className="mr-2 h-4 w-4" />
                    Export
                </Button>
            )}
        </>
    );
}
