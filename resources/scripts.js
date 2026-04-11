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
    var voteLabels = {
        en: { up: 'Vote up', down: 'Vote down' },
        de: { up: 'Positiv bewerten', down: 'Negativ bewerten' },
        fr: { up: 'Vote positif', down: 'Vote négatif' }
    };
    var segments = location.pathname.replace(/^\/|\/$/g, '').split('/');
    if (segments.length === 3 && segments[1] !== 'tag') {
        var votePath = segments[1] + '/' + segments[2];
        var lang = segments[0] || 'en';
        var labels = voteLabels[lang] || voteLabels.en;
        var voting = document.createElement('div');
        voting.className = 'voting';
        voting.setAttribute('data-vote-path', votePath);
        var upBtn = document.createElement('button');
        upBtn.className = 'vote-up';
        upBtn.setAttribute('aria-label', labels.up);
        upBtn.innerHTML = '&#x1F44D;';
        var upCount = document.createElement('span');
        upCount.className = 'vote-up-count';
        upCount.textContent = '0';
        var downBtn = document.createElement('button');
        downBtn.className = 'vote-down';
        downBtn.setAttribute('aria-label', labels.down);
        downBtn.innerHTML = '&#x1F44E;';
        var downCount = document.createElement('span');
        downCount.className = 'vote-down-count';
        downCount.textContent = '0';
        voting.appendChild(upBtn);
        voting.appendChild(upCount);
        voting.appendChild(downBtn);
        voting.appendChild(downCount);
        var target = document.getElementById('voting-target');
        if (target) {
            target.appendChild(voting);
        } else {
            document.querySelector('main').appendChild(voting);
        }
        fetch('/votes/' + votePath)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                upCount.textContent = data.up;
                downCount.textContent = data.down;
            });
        var readStartTime = Date.now();
        var readAccumulated = 0;
        var readVisible = !document.hidden;
        var readTimeSent = false;
        var readSessionId = Math.random().toString(36).substr(2, 12);
        function sendReadTime() {
            var total = readAccumulated;
            if (readVisible) {
                total += Date.now() - readStartTime;
            }
            if (total < 5000) return;
            var seconds = Math.min(Math.round(total / 1000), 3600);
            navigator.sendBeacon('/readtime/' + votePath, seconds + ':' + readSessionId);
            readTimeSent = true;
        }
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (readVisible) {
                    readAccumulated += Date.now() - readStartTime;
                }
                readVisible = false;
                sendReadTime();
            } else {
                readStartTime = Date.now();
                readVisible = true;
                readTimeSent = false;
            }
        });
        window.addEventListener('pagehide', sendReadTime);
    }
    function loadStatVotes() {
        var paths = {};
        document.querySelectorAll('.vote-up-stat[data-vote-path]').forEach(function(el) {
            var path = el.getAttribute('data-vote-path');
            if (!paths[path]) paths[path] = { up: [], down: [] };
            paths[path].up.push(el);
        });
        document.querySelectorAll('.vote-down-stat[data-vote-path]').forEach(function(el) {
            var path = el.getAttribute('data-vote-path');
            if (!paths[path]) paths[path] = { up: [], down: [] };
            paths[path].down.push(el);
        });
        Object.keys(paths).forEach(function(path) {
            fetch('/votes/' + path)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    paths[path].up.forEach(function(el) { el.textContent = data.up; });
                    paths[path].down.forEach(function(el) { el.textContent = data.down; });
                });
        });
    }
    if (document.querySelector('.vote-up-stat[data-vote-path]')) {
        var scheduleStatVotes = window.requestIdleCallback || function(cb) { setTimeout(cb, 200); };
        scheduleStatVotes(function() {
            loadStatVotes();
            setInterval(loadStatVotes, 60000);
        });
    }
    function formatReadTime(seconds) {
        if (seconds < 60) return seconds + 's';
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        return m + 'm ' + s + 's';
    }
    function loadReadTimes() {
        var els = document.querySelectorAll('.readtime-stat[data-path]');
        var paths = {};
        els.forEach(function(el) {
            var path = el.getAttribute('data-path');
            if (!paths[path]) paths[path] = [];
            paths[path].push(el);
        });
        Object.keys(paths).forEach(function(path) {
            fetch('/readtime/' + path)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var text = data.sessions > 0 ? formatReadTime(data.average) : '-';
                    var title = data.sessions + ' session' + (data.sessions !== 1 ? 's' : '');
                    paths[path].forEach(function(el) {
                        el.textContent = text;
                        el.setAttribute('title', title);
                    });
                });
        });
    }
    if (document.querySelector('.readtime-stat[data-path]')) {
        var scheduleReadTimes = window.requestIdleCallback || function(cb) { setTimeout(cb, 200); };
        scheduleReadTimes(function() {
            loadReadTimes();
            setInterval(loadReadTimes, 60000);
        });
    }
    function loadLangStats() {
        fetch('/lang-stats')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var tbody = document.getElementById('lang-stats-body');
                if (!tbody) return;
                tbody.innerHTML = '';
                Object.keys(data).forEach(function(month) {
                    var row = document.createElement('tr');
                    var monthCell = document.createElement('td');
                    monthCell.textContent = month;
                    row.appendChild(monthCell);
                    ['en', 'de', 'fr'].forEach(function(lang) {
                        var cell = document.createElement('td');
                        cell.textContent = data[month][lang] || 0;
                        row.appendChild(cell);
                    });
                    tbody.appendChild(row);
                });
            });
    }
    if (document.getElementById('lang-stats-body')) {
        var scheduleLangStats = window.requestIdleCallback || function(cb) { setTimeout(cb, 200); };
        scheduleLangStats(function() {
            loadLangStats();
            setInterval(loadLangStats, 60000);
        });
    }
    function loadAdStats() {
        fetch('/ad-stats')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var tbody = document.getElementById('ad-stats-body');
                if (!tbody) return;
                tbody.innerHTML = '';
                data.forEach(function(entry) {
                    var row = document.createElement('tr');
                    ['month', 'leaderboard', 'banner', 'mobile', 'unique'].forEach(function(key) {
                        var cell = document.createElement('td');
                        cell.textContent = key === 'month' ? entry[key] : (entry[key] || 0) + ' ' + viewsLabel;
                        row.appendChild(cell);
                    });
                    tbody.appendChild(row);
                });
            });
    }
    if (document.getElementById('ad-stats-body')) {
        var scheduleAdStats = window.requestIdleCallback || function(cb) { setTimeout(cb, 200); };
        scheduleAdStats(function() {
            loadAdStats();
            setInterval(loadAdStats, 60000);
        });
    }
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.vote-up, .vote-down');
        if (!btn) return;
        var voting = btn.closest('.voting[data-vote-path]');
        if (!voting) return;
        var path = voting.getAttribute('data-vote-path');
        var direction = btn.classList.contains('vote-up') ? 'up' : 'down';
        fetch('/vote/' + path, { method: 'POST', body: direction })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                voting.querySelector('.vote-up-count').textContent = data.up;
                voting.querySelector('.vote-down-count').textContent = data.down;
            });
    });
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
