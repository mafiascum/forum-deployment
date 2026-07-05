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
    setPauseState(url, true);
}

function unpauseGame(url) {
    setPauseState(url, false);
}

function setPauseState(url, paused) {
    fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(res => res.json()).then(data => {
        renderPauseBanner(paused, data.paused_at_formatted || '');
    }).catch(err => console.error('Failed to update pause state', err));
}

function renderPauseBanner(paused, formattedTime) {
    const container = document.getElementById('manage-game-pause');
    if (!container) {
        return;
    }

    const pauseUrl = container.dataset.pauseUrl;
    const unpauseUrl = container.dataset.unpauseUrl;

    container.classList.toggle('is-paused', paused);

    const bannerParts = ['<div class="pause-banner">'];
    if (paused) {
        bannerParts.push(
            '<span class="pause-status">Paused at <strong id="pause-timestamp"></strong></span>' +
            '<input type="button" class="button1" value="Unpause" onclick="unpauseGame(\'' + unpauseUrl + '\')" />'
        );
    } else {
        bannerParts.push(
            '<span class="pause-status">Vote counter is running.</span>' +
            '<input type="button" class="button1" value="Pause" onclick="pauseGame(\'' + pauseUrl + '\')" />'
        );
    }
    bannerParts.push('</div>');

    if (paused) {
        bannerParts.push(
            '<div class="pause-disclaimer">' +
            '<strong>Warning:</strong> The vote counter is paused. Any votes cast while paused will not be tracked, and no vote counts will be posted automatically. Unpause to resume tracking.' +
            '</div>'
        );
    }

    container.innerHTML = bannerParts.join('');

    if (paused) {
        const ts = document.getElementById('pause-timestamp');
        if (ts) {
            ts.textContent = formattedTime;
        }
    }
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
