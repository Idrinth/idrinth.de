(function() {
    const modeSelect = document.getElementById('mode-select');
    if (modeSelect) {
        const current = document.documentElement.className.match(/theme-(\w+)/);
        if (current) modeSelect.value = current[1];
    }
    const viewsLabel = document.body.getAttribute('data-views-label') || 'views';
    const uniqueViewsTitle = document.body.getAttribute('data-unique-views-title') || 'At least {count} unique views.';
    function updateCategoryViews() {
        var categoryTotals = {};
        document.querySelectorAll('.views[data-category]').forEach(function(el) {
            var cat = el.getAttribute('data-category');
            var count = parseInt(el.textContent, 10) || 0;
            categoryTotals[cat] = (categoryTotals[cat] || 0) + count;
        });
        document.querySelectorAll('.category-views[data-category]').forEach(function(el) {
            var cat = el.getAttribute('data-category');
            el.textContent = (categoryTotals[cat] || 0) + ' ' + viewsLabel;
        });
        var categoryUniqueTotals = {};
        document.querySelectorAll('.unique-views[data-category]').forEach(function(el) {
            var cat = el.getAttribute('data-category');
            var count = parseInt(el.textContent, 10) || 0;
            categoryUniqueTotals[cat] = (categoryUniqueTotals[cat] || 0) + count;
        });
        document.querySelectorAll('.category-unique-views[data-category]').forEach(function(el) {
            var cat = el.getAttribute('data-category');
            el.textContent = (categoryUniqueTotals[cat] || 0) + ' ' + viewsLabel;
        });
    }
    function updateViews() {
        var paths = {};
        document.querySelectorAll('.views[data-path]').forEach(function(el) {
            var path = el.getAttribute('data-path');
            if (!paths[path]) paths[path] = { views: [], unique: [] };
            paths[path].views.push(el);
        });
        document.querySelectorAll('.unique-views[data-path]').forEach(function(el) {
            var path = el.getAttribute('data-path');
            if (!paths[path]) paths[path] = { views: [], unique: [] };
            paths[path].unique.push(el);
        });
        var promises = Object.keys(paths).map(function(path) {
            return fetch('/views/' + path)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    paths[path].views.forEach(function(el) {
                        el.textContent = data.views + ' ' + viewsLabel;
                        el.setAttribute('title', uniqueViewsTitle.replace('{count}', data.unique));
                    });
                    paths[path].unique.forEach(function(el) {
                        el.textContent = data.unique + ' ' + viewsLabel;
                    });
                });
        });
        if (document.querySelector('.category-views[data-category]')) {
            Promise.all(promises).then(updateCategoryViews);
        }
    }
    if (document.querySelector('.views[data-path]') || document.querySelector('.unique-views[data-path]')) {
        var scheduleViews = window.requestIdleCallback || function(cb) { setTimeout(cb, 200); };
        scheduleViews(function() {
            updateViews();
            setInterval(updateViews, 60000);
        });
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
    var searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.removeAttribute('hidden');
        var lang = document.documentElement.getAttribute('lang') || 'en';
        var wordsData = null;
        var noResultsText = searchForm.getAttribute('data-no-results') || 'No results found.';
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var query = document.getElementById('search-input').value.trim();
            if (!query) return;
            function doSearch(data) {
                var words = query.toLowerCase().replace(/[^\p{L}\p{N}\s]/gu, '').split(/\s+/).filter(function(w) { return w.length >= 1; });
                var scores = {};
                words.forEach(function(word) {
                    if (data.terms[word]) {
                        var entries = data.terms[word];
                        Object.keys(entries).forEach(function(idx) {
                            scores[idx] = (scores[idx] || 0) + entries[idx];
                        });
                    }
                });
                var results = Object.keys(scores)
                    .map(function(idx) { return { path: data.paths[idx], score: scores[idx] }; })
                    .sort(function(a, b) { return b.score - a.score; })
                    .slice(0, 12);
                showSearchResults(results, lang, noResultsText);
            }
            if (wordsData) {
                doSearch(wordsData);
            } else {
                fetch('/words-' + lang + '.json')
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        wordsData = data;
                        doSearch(data);
                    });
            }
        });
    }
    function showSearchResults(results, lang, noResultsText) {
        var existing = document.querySelector('.search-modal-overlay');
        if (existing) existing.remove();
        var overlay = document.createElement('div');
        overlay.className = 'search-modal-overlay';
        var modal = document.createElement('div');
        modal.className = 'search-modal';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        var closeBtn = document.createElement('button');
        closeBtn.className = 'search-modal-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.setAttribute('aria-label', 'Close');
        closeBtn.addEventListener('click', function() { overlay.remove(); });
        modal.appendChild(closeBtn);
        if (results.length === 0) {
            var p = document.createElement('p');
            p.textContent = noResultsText;
            modal.appendChild(p);
        } else {
            var ul = document.createElement('ul');
            results.forEach(function(result) {
                var li = document.createElement('li');
                var a = document.createElement('a');
                a.href = '/' + lang + '/' + result.path;
                var slug = result.path.split('/').pop();
                a.textContent = slug.replace(/-/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
                li.appendChild(a);
                ul.appendChild(li);
            });
            modal.appendChild(ul);
        }
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) overlay.remove();
        });
        var escHandler = function(e) {
            if (e.key === 'Escape') {
                overlay.remove();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
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
