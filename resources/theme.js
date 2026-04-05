(function() {
    let mode = '';
    const cookies = document.cookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        const c = cookies[i].trim();
        if (c.indexOf('mode=') === 0) {
            mode = c.substring(5);
            break;
        }
    }
    if (mode !== 'light' && mode !== 'dark' && mode !== 'auto') {
        mode = 'auto';
    }
    document.documentElement.classList.add('theme-' + mode);
})();
