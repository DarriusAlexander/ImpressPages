<?php

/**
 * requires PHP 5.4, gettext, nodejs and nvm install po2json
 *
 */

$rootDir = dirname(dirname(__DIR__));

$dirs = array(
    'Ip',
    'Plugin',
    'Theme'
);

chdir($rootDir);

if (file_exists("$rootDir/phpunit/tools/tmp_gettext_files.txt")) {
    unlink("$rootDir/phpunit/tools/tmp_gettext_files.txt");
}

foreach ($dirs as $dir) {
    `find ./$dir -iname "*.php" >> {$rootDir}/phpunit/tools/tmp_gettext_files.txt`;
}

`xgettext -f {$rootDir}/phpunit/tools/tmp_gettext_files.txt -L PHP --from-code=utf-8 --keyword=__:1,2c --keyword=_e:1,2c -o {$rootDir}/phpunit/tools/all.po --omit-header`;
`rm {$rootDir}/phpunit/tools/tmp_gettext_files.txt`;

chdir($rootDir . '/phpunit/tools');

`po2json all.po all.json`;
`rm all.po`;

$all = json_decode(file_get_contents(__DIR__ . '/all.json'), true);

$t = array();

$unicodeChar = '\u0004';
$delimiter = json_decode('"' . $unicodeChar . '"');

$domains = array();

foreach ($all as $key => $values) {

    $parts = explode($delimiter, $key, 2);
    if (count($parts) != 2) {
        echo "error encountered on key: $key";
        exit();
    }

    list($domain, $id) = $parts;

    $domains[$domain][$id] = $id;
}

foreach (array('ipPublic', 'ipAdmin') as $domain) {
    $messages = json_decode(file_get_contents($rootDir . '/Ip/Translator/translations/en/' . $domain . '.json'), true);

    $domains[$domain] = array_merge($domains[$domain], $messages);
}

`rm all.json`;

foreach ($domains as $domain => $messageList) {
    file_put_contents(__DIR__ . '/' . $domain . '-en.json', json_encode($messageList, JSON_PRETTY_PRINT)); // JSON_PRETTY_PRINT is only for PHP 5.4 and above
}