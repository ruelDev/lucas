import { Button } from '@/components/ui/button';
import { FileText } from 'lucide-react';

export default function GenerateCfpButton() {
    const handleGenerateCfp = () => {

        // Open JasperServer report in a new tab
        window.open(route('cfp.generateFromJasperServer'), '_blank');
    };

    return (
        <div>
            <Button
                onClick={handleGenerateCfp}
                className="h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
            >
                <FileText className="h-4 w-4" />
                Generate CFP
            </Button>
        </div>
    );
}
