<?php

return PhpCsFixer\Config::create()
    ->setLineEnding("\n")
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(['src'])
    )
    ->setCacheFile('var/cache/php_cs.json')
;