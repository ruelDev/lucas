/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import useSwalOptions from '@/hooks/useSwalOptions';
import { router } from '@inertiajs/react';
import { FileInput } from 'lucide-react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function SendToAuthor({ data }) {
    const ReactSwal = withReactContent(Swal);

    const { swalLoading, onSuccess } = useSwalOptions();

    const onSubmitSendtoAuthor = async () => {
        const result = await ReactSwal.fire({
            title: 'Are you sure you want to send to author?',
            text: 'The receipt will be submitted for authorization.',
            icon: 'warning',
            iconColor: '#1B4298',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Yes, send it!',
            confirmButtonColor: '#1B4298',
            showCancelButton: true,
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            router.post(
                route('out-collection.authorization.sent-to-author'),
                { data },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onStart: () => swalLoading('Sending to Author'),
                    onSuccess: (res) => onSuccess(res),
                    onError: () => ReactSwal.close(),
                },
            );
        }
    };

    return (
        <Button onClick={onSubmitSendtoAuthor} className="submit-button disabled:cursor-not-allowed">
            <FileInput className="h-4 w-4" />
            Send to Author
        </Button>
    );
}
