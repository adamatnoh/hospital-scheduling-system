import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline';
import Swal from 'sweetalert2';

const element = document.getElementById('hss-calendar');

if (element) {
    const mobile = window.matchMedia('(max-width: 767px)').matches;
    const calendar = new Calendar(element, {
        plugins: [dayGridPlugin, listPlugin, interactionPlugin, resourceTimelinePlugin],
        schedulerLicenseKey: import.meta.env.VITE_FULLCALENDAR_LICENSE || 'CC-Attribution-NonCommercial-NoDerivatives',
        initialView: mobile ? 'listWeek' : 'resourceTimelineMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: mobile ? 'listWeek,dayGridMonth' : 'resourceTimelineMonth,dayGridMonth,listWeek',
        },
        resourceAreaHeaderContent: 'Doctors',
        resources: element.dataset.resourcesUrl,
        events: element.dataset.eventsUrl,
        editable: element.dataset.editable === '1',
        height: 'auto',
        eventDrop(info) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(`/assignments/${info.event.id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token ?? '',
                },
                body: JSON.stringify({ date: info.event.startStr.slice(0, 10) }),
            }).then(async (response) => {
                if (! response.ok) {
                    info.revert();
                    const body = await response.json().catch(() => ({}));
                    Swal.fire('Not moved', body.message || 'That change would double-book a doctor.', 'warning');
                }
            });
        },
    });

    calendar.render();
}
