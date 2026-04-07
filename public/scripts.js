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
var promises = [];
document.querySelectorAll('.views[data-path]').forEach(function(el) {
var path = el.getAttribute('data-path');
var p = fetch('/views/' + path)
.then(function(r) { return r.text(); })
.then(function(count) { el.textContent = count + ' ' + viewsLabel; });
promises.push(p);
fetch('/unique-views/' + path)
.then(function(r) { return r.text(); })
.then(function(count) { el.setAttribute('title', uniqueViewsTitle.replace('{count}', count)); });
});
document.querySelectorAll('.unique-views[data-path]').forEach(function(el) {
var p = fetch('/unique-views/' + el.getAttribute('data-path'))
.then(function(r) { return r.text(); })
.then(function(count) { el.textContent = count + ' ' + viewsLabel; });
promises.push(p);
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