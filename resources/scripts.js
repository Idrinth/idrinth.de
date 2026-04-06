(function() {
    const modeSelect = document.getElementById('mode-select');
    if (modeSelect) {
        const current = document.documentElement.className.match(/theme-(\w+)/);
        if (current) modeSelect.value = current[1];
    }
    const viewsLabel = document.body.getAttribute('data-views-label') || 'views';
    function updateCategoryViews() {
        var categoryTotals = {};
        var categoryElements = document.querySelectorAll('.views[data-category]');
        categoryElements.forEach(function(el) {
            var cat = el.getAttribute('data-category');
            var count = parseInt(el.textContent, 10) || 0;
            categoryTotals[cat] = (categoryTotals[cat] || 0) + count;
        });
        document.querySelectorAll('.category-views[data-category]').forEach(function(el) {
            var cat = el.getAttribute('data-category');
            el.textContent = (categoryTotals[cat] || 0) + ' ' + viewsLabel;
        });
    }
    function updateViews() {
        var promises = [];
        document.querySelectorAll('.views[data-path]').forEach(function(el) {
            var p = fetch('/views/' + el.getAttribute('data-path'))
                .then(function(r) { return r.text(); })
                .then(function(count) { el.textContent = count + ' ' + viewsLabel; });
            promises.push(p);
        });
        if (document.querySelector('.category-views[data-category]')) {
            Promise.all(promises).then(updateCategoryViews);
        }
    }
    if (document.querySelector('.views[data-path]')) {
        updateViews();
        setInterval(updateViews, 60000);
    }
    var languageSelect = document.getElementById('language-select');
    if (languageSelect) {
        languageSelect.addEventListener('change', function() {
            var expires = new Date().getTime() + 86400 * 30 * 1000;
            document.cookie = 'language=' + this.value + '; expires=' + expires + '; domain=' + location.hostname + '; path=/';
            var p = location.pathname.replace(/^\/(en|fr|de)(\/|$)/, '/' + this.value + '$2');
            if (p === location.pathname) p = '/' + this.value + location.pathname;
            location.href = p;
        });
    }
    if (modeSelect) {
        modeSelect.addEventListener('change', function() {
            var expires = new Date().getTime() + 86400 * 30 * 1000;
            document.cookie = 'mode=' + this.value + '; expires=' + expires + '; domain=' + location.hostname + '; path=/';
            document.documentElement.className = document.documentElement.className.replace(/theme-\w+/, 'theme-' + this.value);
        });
    }
    var donateButton = document.getElementById('donate-button');
    if (donateButton && donateButton.dataset.paypalId && typeof PayPal !== 'undefined') {
        PayPal.Donation.Button({
            env: 'production',
            hosted_button_id: donateButton.dataset.paypalId,
            image: {
                src: donateButton.dataset.donateSrc,
                alt: donateButton.dataset.donateAlt,
                title: 'PayPal - The safer, easier way to pay online!',
            }
        }).render('#donate-button');
    }
    var adLink = document.getElementById('ad-link');
    if (adLink) {
        fetch('/ad.lnk')
            .then(function(r) { return r.text(); })
            .then(function(href) {
                adLink.setAttribute('href', href);
                var adImg = adLink.querySelector('img');
                if (adImg && adLink.dataset.adTitle) {
                    adImg.setAttribute('title', adLink.dataset.adTitle.replace('{href}', href));
                }
            });
    }
})();
