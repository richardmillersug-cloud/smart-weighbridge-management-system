<script>
    (() => {
        try {
            const theme = localStorage.getItem('woms-theme') || 'dark';
            document.documentElement.classList.toggle('light', theme === 'light');
        } catch (_) {
            // Keep the default dark theme when storage is unavailable.
        }
    })();
</script>
