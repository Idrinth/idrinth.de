<?php

define("ROOT_DIR", dirname(__DIR__));

$template = file_get_contents(ROOT_DIR . '/templates/template.html');

$releases = [];
foreach (scandir(ROOT_DIR . '/posts') as $folder) {
    $meta = json_decode(file_get_contents(ROOT_DIR . '/posts/' . $folder . '/meta.json'));
    $releases[$folder] = $meta;
    if (is_dir(ROOT_DIR . '/output/' . $folder)) {
        continue;
    }
    $english = file_get_contents(ROOT_DIR . '/posts/' . $folder . '/en.md');
    $german = file_get_contents(ROOT_DIR . '/posts/' . $folder . '/de.md');
    $french = file_get_contents(ROOT_DIR . '/posts/' . $folder . '/fr.md');
}
