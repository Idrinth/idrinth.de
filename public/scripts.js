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
})();