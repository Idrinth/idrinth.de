# CLAUDE.md - Project Guide for idrinth.de

## Overview

Personal multilingual blog (English, German, French) built with vanilla PHP. No frameworks, no package managers, zero external dependencies. Static HTML is pre-generated from Markdown content and served via Apache.

Live at: https://idrinth.de

## Tech Stack

- **Language**: PHP 8.3+
- **Server**: Apache with mod_rewrite
- **Frontend**: Vanilla HTML, CSS, JavaScript (no build tools, no npm)
- **Content format**: Markdown + JSON metadata
- **Template engine**: Custom string replacement (`###VARIABLE###` placeholders)

## Project Structure

```
bin/
  cron.php              # Static site generator (the build script)
  merge-viewcounts.php  # View count aggregation utility
posts/
  <slug>/
    meta.json           # Category, tags, date
    en.md               # English content
    de.md               # German content
    fr.md               # French content
resources/
  en/ de/ fr/           # Per-language HTML templates
  *.css, *.js           # Source CSS/JS (unminified)
  *.xml, *.html         # Feed and component templates
output/
  posts.json            # Index of all post URLs
  <category>/<slug>/    # Generated HTML per language + viewcount.txt
  *.rss, *.atom         # Generated feeds
public/
  index.php             # Runtime router / entry point
  .htaccess             # Apache rewrite rules and cache headers
  styles.css, scripts.js, theme.js  # Minified assets (generated)
ads/
  YYYY-MM/              # Monthly ad images (leaderboard, banner, mobile)
  0000-00/              # Fallback ads
.github/workflows/
  build.yml             # CI: runs build on push to master
```

## Build System

The site generator is `bin/cron.php`. It:

1. Loads all templates from `resources/`
2. Minifies CSS, JS, and HTML
3. Parses Markdown posts into HTML
4. Generates per-language pages for every post, category, tag, and static page
5. Generates RSS/Atom feeds and sitemap.xml
6. Writes everything to `output/` and `public/`

### Running the build

```bash
php bin/cron.php
```

The generated `output/` and `public/` files are version-controlled but should only be committed by the CI runner (GitHub Actions), not manually. The build detects template changes via MD5 hashing (`.template-hash`) and regenerates only when needed (or when post content changes).

## Routing (public/index.php)

PHP router with no framework. Key routes:

- `/{lang}/` - Home page (latest 9 posts)
- `/{lang}/{category}/{slug}` - Individual post
- `/{lang}/tag/{tag-name}` - Posts by tag
- `/{lang}/{category}` - Posts by category
- `/{lang}/feed.rss`, `/{lang}/feed.atom` - Feeds (also per-category/tag)
- `/views/{path}` - View counter API (increments on page load)
- `/random` - Redirects to a random post
- `/ad/*` - Ad image serving (monthly rotation)
- `/imprint`, `/thank-you`, `/canceled`, `/statistics` - Static pages

Language detection priority: URL prefix > cookie > Accept-Language header. Redirects to `/{lang}/...` if no language in URL.

## Content Authoring

### Adding a new post

1. Create `posts/<slug>/` directory
2. Add `meta.json`:
   ```json
   {
     "category": "software-engineering",
     "tags": ["php", "static site"],
     "date": "2026-04-05"
   }
   ```
3. Add `en.md`, `de.md`, `fr.md` with Markdown content
4. Run `php bin/cron.php`

### Valid categories

`software-engineering`, `open-source`, `games`, `streaming`, `modding`, `stories`, `pen-and-paper`, `world-building`

### Markdown support

Custom parser in `cron.php`. Supports: headings (`#`-`######`), unordered lists (`-`), ordered lists (`1.`), paragraphs, inline bold (`**`), italic (`*`), links (`[text](url)`), inline code (`` ` ``). First `#` heading becomes the page title.

## Internationalization

Three languages: `en`, `de`, `fr`. Translations are defined as arrays in `bin/cron.php` (the `$translations` variable). Every post must have all three language files. All pages are generated in all three languages with hreflang tags.

## CI/CD

GitHub Actions (`.github/workflows/build.yml`):
- Triggers on push to `master`
- Sets up PHP 8.3
- Runs `php bin/cron.php`
- Auto-commits regenerated `output/` and `public/` files

## Testing

No automated test suite exists. Verify changes by:
1. Running `php bin/cron.php` and checking for PHP errors
2. Inspecting generated HTML in `output/`
3. Optionally serving locally with `php -S localhost:8080 -t public/`

## Linting

No linter or formatter is configured.

## Key Design Decisions

- **Zero dependencies**: No Composer, no npm. Everything is vanilla PHP/HTML/CSS/JS.
- **Generated output is committed**: The `output/` directory is version-controlled so the site can be deployed by simply serving the repo.
- **View counts use file locking**: `viewcount.txt` files with `flock()` for safe concurrent writes.
- **Template variables**: All templates use `###VARIABLE###` style placeholders replaced via `str_replace`.
- **Cache busting**: Asset URLs get a query parameter based on file content hash.
- **No database**: All data lives in the filesystem (Markdown files, JSON, text files).

## Files to Never Edit or Commit

- `output/**/*.html` - Generated by `bin/cron.php`
- `output/**/*.rss`, `output/**/*.atom` - Generated feeds
- `output/posts.json` - Generated post index
- `output/sitemap.xml` - Generated sitemap
- `public/styles.css`, `public/scripts.js`, `public/theme.js` - Minified from `resources/`

Edit the source files in `resources/` and `posts/` instead, then regenerate.

**WARNING: Do not commit changes to generated files.** The GitHub Actions CI runner automatically regenerates and commits these files on push to `master`. Committing generated file changes manually will lead to merge conflicts.
