const POSTING_USER_ID = 3467;

async function post_to_topic(button) {
    console.log("POSTING TO TOPIC AHH");

    const topicId = button.dataset.topicId;
    const endpoint = button.dataset.endpoint;
    const content = "TestyMcTestFace";

    const formData = new FormData();
    formData.append('topic_id', topicId);
    formData.append('user_id', POSTING_USER_ID);
    formData.append('message', content);

    const response = await fetch(endpoint, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    });


    const result = await response.json();
    console.log(result);
}
