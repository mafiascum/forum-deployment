function add_user_as_slot(inputElemId, topicId) {
    const inputElem = $(`#${inputElemId}`);
    const username = inputElem.val().trim();
    if (!username) return;
    const endpoint = `${inputElem.attr('data-endpoint')}?q=${encodeURIComponent(username)}&t=${topicId}`;
    fetch(endpoint)
        .then(r => {
            if (!r.ok) throw new Error(`Server returned ${r.status}`);

            console.log("BEFORE");
            let text = r.text();

            console.log("AFTER");

            return text;
        })
        .then(text => {

            const data = JSON.parse(text);


            console.log(data);
            if (!data || !data.partial) {
                console.log("Early Return");
                return;
            };
            const user = data[0];
            $('#vc_slot_list').append(data.partial);
            inputElem.val('');
        }).catch(err => console.error('TEST', err));
}

function remove_slot(el, topicId) {
    const endpoint = `${el.dataset.endpoint}?t=${topicId}&q=${el.dataset.userId}`;
    fetch(endpoint).then(r => {
        if (r.ok) el.closest('li').remove();
    });
}
