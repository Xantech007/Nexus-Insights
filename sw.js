<!-- In admin/includes/header.php or footer.php -->
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js', { scope: '/admin/' })
            .then(() => console.log('Service Worker Registered'))
            .catch((error) => console.error('Service Worker Error:', error));
    }
</script>
