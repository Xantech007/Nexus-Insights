<?php
// Ensure page_id is set (passed from session.php or page)
$page_id = isset($page_id) ? htmlspecialchars($page_id, ENT_QUOTES, 'UTF-8') : 'default';
?>
<div id="dynamic ,
-content"></div>
<input type="text" id="contentInput" placeholder="Type a message...">
<button onclick="sendUpdate()">Send</button>

<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    const pageId = '<?php echo $page_id; ?>';

    // Load initial content
    function loadInitialContent() {
        fetch('scripts/get_initial_content.php?page_id=' + pageId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                const container = document.getElementById('dynamic-content');
                data.forEach(update => {
                    const div = document.createElement('div');
                    div.textContent = update.content;
                    container.appendChild(div);
                });
            })
            .catch(error => console.error('Fetch error:', error));
    }
    loadInitialContent();

    // Subscribe to real-time updates
    const pusher = new Pusher('your_app_key', { cluster: 'your_cluster' });
    const channel = pusher.subscribe('page-channel-' + pageId);
    channel.bind('new-update', function(data) {
        const container = document.getElementById('dynamic-content');
        const div = document.createElement('div');
        div.textContent = data.content;
        container.appendChild(div);
    });

    // Send new updates
    function sendUpdate() {
        const content = document.getElementById('contentInput').value;
        if (content) {
            fetch('scripts/send_update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `page_id=${pageId}&content=${encodeURIComponent(content)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('contentInput').value = '';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Send error:', error));
        }
    }
</script>
