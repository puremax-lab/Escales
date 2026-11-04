// Infinite scroll for lists: when the "Charger la suite" link comes into view (or is clicked),
// the next page is fetched and its items are added to the list. Without JavaScript the link
// simply opens the next page.
document.querySelectorAll('[data-load-more]').forEach((link) => {
    const list = document.querySelector(link.dataset.loadMore);
    let loading = false;
    let failed = false;

    async function loadNext(moveFocus) {
        if (loading || !link.isConnected) {
            return;
        }
        loading = true;
        link.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(link.href, { headers: { Accept: 'text/html' } });
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const items = [...page.querySelector(link.dataset.loadMore).children];
            list.append(...items);
            if (moveFocus && items[0]) {
                items[0].focus();
            }
            const next = page.querySelector('[data-load-more]');
            if (next) {
                link.setAttribute('href', next.getAttribute('href'));
                // Observing again checks at once whether the link is still in view (short pages).
                observer.unobserve(link);
                observer.observe(link);
            } else {
                observer.disconnect();
                link.parentElement.remove();
            }
        } catch {
            // From now on the link works as a plain link to the next page.
            failed = true;
            observer.disconnect();
        } finally {
            loading = false;
            link.removeAttribute('aria-busy');
        }
    }

    const observer = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
            loadNext(false);
        }
    }, { rootMargin: '400px' });
    observer.observe(link);

    link.addEventListener('click', (event) => {
        if (failed) {
            return;
        }
        event.preventDefault();
        loadNext(true);
    });
});
