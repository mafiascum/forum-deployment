document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.manage-tab');
    const contentContainer = document.querySelector('#manage-game-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();

            tabs.forEach(t => t.parentElement.classList.remove('activetab'));
            tab.parentElement.classList.add('activetab');

            const url = tab.dataset.url;

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.text())
                .then(html => {
                    contentContainer.innerHTML = html;
                })
                .catch(err => {
                    console.error('Failed to load tab content', err);
                });
        });
    });
});
