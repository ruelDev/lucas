import { Button } from '@/components/ui/button';
import { can } from '@/lib/can';
import { Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

export default function CreateUserButton() {
    return (
        <div>
            {can('user_management.create') && (
                <Button
                    asChild
                    className="h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                >
                    <Link href="/user-management/create">
                        <Plus className="h-4 w-4" />
                        Create User
                    </Link>
                </Button>
            )}
        </div>
    );
}
