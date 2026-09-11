/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import useSwalOptions from '@/hooks/useSwalOptions';
import { router } from '@inertiajs/react';
import { FilePen } from 'lucide-react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function Authorize({ data }) {
    const ReactSwal = withReactContent(Swal);

    const { swalLoading, onSuccess } = useSwalOptions();

    const onSubmit = async (e) => {
        e.preventDefault();

        const result = await ReactSwal.fire({
            title: 'Confirm Authorize?',
            text: 'Are you sure you want to authorize these receipt?',
            icon: 'warning',
            iconColor: '#1B4298',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Yes, authorize!',
            confirmButtonColor: '#1B4298',
            showCancelButton: true,
            reverseButtons: true,
        });

        if (!result.isConfirmed) return;

        if (result.isConfirmed) {
            router.post(
                route('out-collection.authorization.authorize'),
                { data },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onStart: () => swalLoading('Authorizing'),
                    onSuccess: (res) => onSuccess(res),
                    onError: () => ReactSwal.close(),
                },
            );
        }
    };

    return (
        <Button onClick={onSubmit} className="submit-button disabled:cursor-not-allowed">
            <FilePen className="h-4 w-4" />
            Authorize
        </Button>
    );
}
