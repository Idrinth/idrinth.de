(function() {
    const modeSelect = document.getElementById('mode-select');
    if (modeSelect) {
        const current = document.documentElement.className.match(/theme-(\w+)/);
        if (current) modeSelect.value = current[1];
    }
    const viewsLabel = document.body.getAttribute('data-views-label') || 'views';
    function updateViews() {
        document.querySelectorAll('.views[data-path]').forEach(function(el) {
            fetch('/views/' + el.getAttribute('data-path'))
                .then(function(r) { return r.text(); })
                .then(function(count) { el.textContent = count + ' ' + viewsLabel; });
        });
    }
    if (document.querySelector('.views[data-path]')) {
        updateViews();
        setInterval(updateViews, 60000);
    }
})();
