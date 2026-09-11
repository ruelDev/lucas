/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import useSwalOptions from '@/hooks/useSwalOptions';
import { useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function Delete({ deposit_id, id, disabled }) {
    const ReactSwal = withReactContent(Swal);

    const { delete: destroy } = useForm();

    const { swalLoading, onSuccess } = useSwalOptions();

    const handleDelete = (e) => {
        e.preventDefault();

        ReactSwal.fire({
            title: 'Confirm Delete?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            iconColor: '#1B4298',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Confirm Delete',
            confirmButtonColor: '#D63031',
            showCancelButton: true,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                ReactSwal.fire({
                    title: <p>Deleting...</p>,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        ReactSwal.showLoading();
                    },
                });

                destroy(route('out-collection.view-deposit.destroy-payment', { deposit: deposit_id, payment: id }), {
                    preserveState: true,
                    preserveScroll: true,
                    onStart: () => swalLoading('Deleting'),
                    onSuccess: (res) => onSuccess(res),
                    onError: () => ReactSwal.close(),
                });
            }
        });
    };
    return (
        <Button
            disabled={disabled}
            onClick={handleDelete}
            variant="ghost"
            size="icon"
            className="text-red-500 hover:bg-red-100 hover:text-red-500"
            title="Delete"
        >
            <Trash2 className={`size-5 text-red-500 ${disabled ? 'opacity-50' : ''}`} />
        </Button>
    );
}
