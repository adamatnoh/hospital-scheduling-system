/**
 * HSS announcement carousel (no framework needed, so it also runs on the
 * public landing page). Successor to the 2023 setInterval slideshow.
 */
const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function initCarousel(root) {
    const track = root.querySelector('[data-carousel-track]');
    const slides = [...root.querySelectorAll('[data-carousel-slide]')];
    if (! track || slides.length < 2) {
        return;
    }

    const dots = [...root.querySelectorAll('[data-carousel-dot]')];
    const counter = root.querySelector('[data-carousel-counter]');
    const toggle = root.querySelector('[data-carousel-toggle]');
    const progress = root.querySelector('[data-carousel-progress]');
    const interval = Number(root.dataset.interval || 6000);

    let index = 0;
    let timer = null;
    let userPaused = reducedMotion();
    let hoverPaused = false;
    let startedAt = 0;
    let frame = null;

    const go = (next, { announce = false } = {}) => {
        index = (next + slides.length) % slides.length;
        track.style.transform = `translateX(-${index * 100}%)`;
        slides.forEach((slide, i) => {
            const active = i === index;
            slide.toggleAttribute('aria-hidden', ! active);
            slide.querySelectorAll('[data-carousel-focusable]').forEach((el) => {
                active ? el.removeAttribute('tabindex') : el.setAttribute('tabindex', '-1');
            });
        });
        dots.forEach((dot, i) => (i === index ? dot.setAttribute('aria-current', 'true') : dot.removeAttribute('aria-current')));
        if (counter) {
            counter.textContent = `${index + 1} / ${slides.length}`;
        }
        // Screen readers only hear slide changes the user asked for.
        track.setAttribute('aria-live', announce ? 'polite' : 'off');
        restart();
    };

    const tick = () => {
        if (! progress) {
            return;
        }
        const ratio = Math.min(1, (performance.now() - startedAt) / interval);
        progress.style.transform = `scaleX(${ratio})`;
        frame = requestAnimationFrame(tick);
    };

    const stop = () => {
        clearTimeout(timer);
        cancelAnimationFrame(frame);
        timer = null;
    };

    function restart() {
        stop();
        if (progress) {
            progress.style.transform = 'scaleX(0)';
        }
        if (userPaused || hoverPaused || document.hidden) {
            return;
        }
        startedAt = performance.now();
        timer = setTimeout(() => go(index + 1), interval);
        frame = requestAnimationFrame(tick);
    }

    const renderToggle = () => {
        if (! toggle) {
            return;
        }
        toggle.setAttribute('aria-label', userPaused ? 'Play announcements' : 'Pause announcements');
        toggle.innerHTML = `<i class="bi ${userPaused ? 'bi-play-fill' : 'bi-pause-fill'}" aria-hidden="true"></i>`;
    };

    root.querySelector('[data-carousel-prev]')?.addEventListener('click', () => go(index - 1, { announce: true }));
    root.querySelector('[data-carousel-next]')?.addEventListener('click', () => go(index + 1, { announce: true }));
    dots.forEach((dot) => dot.addEventListener('click', () => go(Number(dot.dataset.carouselDot), { announce: true })));
    toggle?.addEventListener('click', () => {
        userPaused = ! userPaused;
        renderToggle();
        restart();
    });

    // Pause on hover and while keyboard focus is inside.
    root.addEventListener('mouseenter', () => { hoverPaused = true; restart(); });
    root.addEventListener('mouseleave', () => { hoverPaused = root.contains(document.activeElement); restart(); });
    root.addEventListener('focusin', () => { hoverPaused = true; restart(); });
    root.addEventListener('focusout', (event) => {
        if (! root.contains(event.relatedTarget)) {
            hoverPaused = root.matches(':hover');
            restart();
        }
    });
    document.addEventListener('visibilitychange', restart);

    root.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') { event.preventDefault(); go(index - 1, { announce: true }); }
        if (event.key === 'ArrowRight') { event.preventDefault(); go(index + 1, { announce: true }); }
    });

    // Swipe / drag.
    const viewport = root.querySelector('[data-carousel-viewport]') ?? root;
    let startX = null;
    let startY = 0;
    viewport.addEventListener('pointerdown', (event) => {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }
        startX = event.clientX;
        startY = event.clientY;
    });
    viewport.addEventListener('pointerup', (event) => {
        if (startX === null) {
            return;
        }
        const dx = event.clientX - startX;
        const dy = event.clientY - startY;
        startX = null;
        if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
            go(dx < 0 ? index + 1 : index - 1, { announce: true });
        }
    });
    viewport.addEventListener('pointercancel', () => { startX = null; });
    viewport.style.touchAction = 'pan-y';

    renderToggle();
    go(0);
}

document.querySelectorAll('[data-carousel]').forEach(initCarousel);
