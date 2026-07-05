const manageTabSelector = '.manage-tab';
const manageGameContentSelector = '#manage-game-content';

document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll(manageTabSelector);
    const contentContainer = document.querySelector(manageGameContentSelector);

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
                    console.error('Failed to load individual votecounter tab content', err);
                });
        });
    });
});

function generateVotecount(url) {
    const asAt = document.getElementById('votecount-as-at').value;
    const body = {};
    if (asAt !== '') {
        body.as_at = asAt;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(body)
    })
        .then(response => response.json())
        .then(data => {
            document.getElementById('votecount-output').value = data.votecount;
        })
        .catch(err => console.error('Failed to generate vote count', err));
}

function pauseGame(url) {
    updatePauseState(url);
}

function unpauseGame(url) {
    updatePauseState(url);
}

function updatePauseState(url) {
    fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(res => {
        if (!res.ok) {
            throw new Error('Pause request failed with status ' + res.status);
        }
        return res.text();
    }).then(html => {
        const container = document.getElementById('manage-game-pause');
        if (container) {
            container.outerHTML = html;
        }
    }).catch(err => {
        console.error('Failed to update pause state', err);
        alert('Failed to update pause state. Please try again.');
    });
}

function createVoteCounter(url) {
    const contentContainer = document.querySelector(manageGameContentSelector);

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(response => response.text()).then(html => {
        contentContainer.innerHTML = html;
    }).catch(err => {
        console.error('Failed to initialize and load form votecounter form content', err);
    });
}

function deletePlayer(url, username) {
    if (!confirm(
        'Remove ' + username + ' from this game?\n\n' +
        'This will permanently delete their vote history for this game. ' +
        'The vote counter will no longer count or display any votes they cast or received. ' +
        'This cannot be undone.'
    )) {
        return;
    }

    fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(res => res.text()).then(html => {
        document.querySelector(manageGameContentSelector).innerHTML = html;
    }).catch(err => console.error('Failed to delete player', err));
}

function deleteDay(url, dayNumber) {
    fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(res => res.text()).then(html => {
        document.querySelector(manageGameContentSelector).innerHTML = html;
    }).catch(err => console.error('Failed to delete day', err));
}

function editPlayer(url) {
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(res => res.text()).then(html => {
        document.querySelector(manageGameContentSelector).innerHTML = html;
    }).catch(err => console.error('Failed to load player edit form', err));
}

function loadPlayerTab(url) {
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(res => res.text()).then(html => {
        document.querySelector(manageGameContentSelector).innerHTML = html;
    }).catch(err => console.error('Failed to load players tab', err));
}

function updatePlayer(url) {
    const username = document.querySelector('#edit-username').value;
    const diedAtEl = document.querySelector('#edit-died-at');
    const died_at = diedAtEl ? diedAtEl.value : '';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ username, died_at })
    }).then(res => res.text()).then(html => {
        document.querySelector(manageGameContentSelector).innerHTML = html;
    }).catch(err => console.error('Failed to update player', err));
}

function editDay(url) {
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(res => res.text()).then(html => {
        document.querySelector(manageGameContentSelector).innerHTML = html;
    }).catch(err => console.error('Failed to load day edit form', err));
}

function loadDayTab(url) {
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(res => res.text()).then(html => {
        document.querySelector(manageGameContentSelector).innerHTML = html;
    }).catch(err => console.error('Failed to load days tab', err));
}

function addDay(url) {
    const day_number = document.querySelector('#day-number').value;
    const start_post_number = document.querySelector('#day-start-post').value;
    const end_post_number = document.querySelector('#day-end-post').value;

    if (!day_number || !start_post_number) {
        return;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ day_number, start_post_number, end_post_number })
    }).then(res => res.text()).then(html => {
        document.querySelector(manageGameContentSelector).innerHTML = html;
    }).catch(err => console.error('Failed to add day', err));
}

function updateDay(url) {
    const day_number = document.querySelector('#edit-day-number').value;
    const start_post_number = document.querySelector('#edit-start-post').value;
    const end_post_number = document.querySelector('#edit-end-post').value;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ day_number, start_post_number, end_post_number })
    }).then(res => res.text()).then(html => {
        document.querySelector(manageGameContentSelector).innerHTML = html;
    }).catch(err => console.error('Failed to update day', err));
}

function addPlayer(url) {
    const username = document.querySelector('#username').value;
    if (!username || username.trim() == '') {
        return; // TODO: Graceful error handling
    }

    console.log(username);

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ username })
    })
        .then(res => res.text())
        .then(res => {
            console.log(res);
            document.querySelector(manageGameContentSelector).innerHTML = res;
        }).catch(err => {
            console.error('Failed to add player', err)
        });
}
