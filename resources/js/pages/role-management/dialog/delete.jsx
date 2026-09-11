/* eslint-disable react/prop-types */
import { can } from '@/lib/can';
import { useForm } from '@inertiajs/react';
import { Trash } from 'lucide-react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function DeleteRoleDialog({ role, closeDropdown }) {
    const { delete: destroy } = useForm();

    const ReactSwal = withReactContent(Swal);

    const handleRoleDelete = (e) => {

        closeDropdown()

        e.preventDefault();
        ReactSwal.fire({
            title: 'Confirm Delete?',
            html: `<p>This action will permanently delete the role</p>`,
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

                destroy(route('role-management.destroy', role.id), {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: (response) => {
                        ReactSwal.fire({
                            title: 'Success',
                            icon: 'success',
                            iconColor: '#1B4298',
                            text: response.props.flash.success,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#1B4298',
                        });
                    },
                    onError: (response) => {
                        if (response.delete_role) {
                            ReactSwal.fire({
                                title: 'Error',
                                text: response.delete_role,
                                icon: 'error',
                                confirmationButtonText: 'OK',
                            });
                        }
                    },
                });
            }
        });
    };

    return (
        <>
            {can('role_management.delete') && (
                <button onClick={handleRoleDelete} className="w-full">
                    <div className="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1 hover:bg-red-100 hover:text-red-600">
                        <Trash className="h-4 w-4" />
                        <span className="text-sm">Delete</span>
                    </div>
                </button>
            )}
        </>
    );
}
