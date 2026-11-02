import './bootstrap';
import Swal from 'sweetalert2';
import './calendar';

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (! (form instanceof HTMLFormElement) || ! form.dataset.confirm) {
        return;
    }
    if (form.dataset.confirmed === '1') {
        return;
    }
    event.preventDefault();
    Swal.fire({
        title: form.dataset.confirm,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#012970',
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
    }).then((result) => {
        if (result.isConfirmed) {
            form.dataset.confirmed = '1';
            form.requestSubmit();
        }
    });
});
