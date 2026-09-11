/* eslint-disable react/prop-types */
import { can } from '@/lib/can';
import { useForm } from '@inertiajs/react';
import { Trash } from 'lucide-react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function DeleteBranchDialog({ branch, closeDropdown }) {
    const { delete: destroy } = useForm();

    const ReactSwal = withReactContent(Swal);

    const handleBranchDelete = (e) => {

        closeDropdown();

        e.preventDefault();
        ReactSwal.fire({
            title: 'Confirm Delete?',
            html: `<p>This action cannot be undone.</p>
            <div style="margin-top: 10px; font-size: 0.8em; font-color: #A9A9A9;">
            <span style="font-weight: 600">Note: </span>
            <span>It's recommended to change the status on edit instead, as the ID and code will remain in the system</span>
            </div>`,
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

                destroy(route('branch-management.destroy', branch.id), {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: (response) => {
                        ReactSwal.fire({
                            title: 'Success',
                            icon: 'success',
                            iconColor: '#1B4298',
                            text: response.props.flash.success,
                            confirmButtonText: 'Ok, got it!',
                            confirmButtonColor: '#1B4298',
                        });
                    },
                    onError: (response) => {
                        if (response.delete_branch) {
                            ReactSwal.fire({
                                title: 'Error',
                                text: response.delete_branch,
                                icon: 'error',
                                confirmationButtonText: 'Ok, got it!',
                                confirmButtonColor: '#1B4298',
                            });
                        }
                    },
                });
            }
        });
    };

    return (
        <>
            {can('branch_management.delete') && (
                <button onClick={handleBranchDelete} className="w-full">
                    <div className="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1 hover:bg-red-100 hover:text-red-600">
                        <Trash className="h-4 w-4" />
                        <span className="text-sm">Delete</span>
                    </div>
                </button>
            )}
        </>
    );
}
