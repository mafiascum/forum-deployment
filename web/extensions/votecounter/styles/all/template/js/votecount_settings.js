function verify_username(inputElemId) {
    const inputElem = $(`#${inputElemId}`);
    let endpoint = inputElem.attr('data-endpoint') + '?q=' + inputElem.val();
    fetch(endpoint).then(response => response.json()).then(data => {
        console.log(data);
        inputElem.val('');
    });
}
