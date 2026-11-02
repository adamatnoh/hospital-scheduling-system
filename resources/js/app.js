import './bootstrap';
import Swal from 'sweetalert2';
import './calendar';
import './carousel';
import './charts';

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (! (form instanceof HTMLFormElement) || ! form.dataset.confirm) {
        return;
    }
    if (form.dataset.confirmed === '1') {
        return;
    }
    event.preventDefault();
    const danger = form.dataset.confirmTone === 'danger';
    Swal.fire({
        title: form.dataset.confirm,
        text: form.dataset.confirmText || undefined,
        icon: danger ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: danger ? '#be123c' : '#117c7a',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, continue',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            form.dataset.confirmed = '1';
            form.requestSubmit();
        }
    });
});
