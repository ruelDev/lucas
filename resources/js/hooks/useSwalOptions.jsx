import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

const ReactSwal = withReactContent(Swal);

export default function useSwalOptions(reset) {
    const swalLoading = (loadingTitle) => {
        ReactSwal.fire({
            title: <p>{loadingTitle ?? 'Please wait'}...</p>,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                ReactSwal.showLoading();
            },
        });
    };

    const swalSuccess = (res) => {
        ReactSwal.fire({
            title: 'Success',
            text: res.props.flash.success,
            icon: 'success',
            iconColor: '#1B4298',
            confirmButtonText: 'Ok, got it!',
            confirmButtonColor: '#1B4298',
        });

        if (reset) reset();
    };

    const swalError = (message) => {
        ReactSwal.fire({
            title: 'Something went wrong.',
            text: message,
            icon: 'error',
            confirmButtonColor: '#1B4298',
        });
    };

    const onSuccess = (res) => {
        if (res.props.flash.success) {
            swalSuccess(res);
        } else if (res.props.flash.error) {
            swalError(res.props.flash.error);
        }
    };

    return { swalLoading, swalSuccess, swalError, onSuccess };
}
