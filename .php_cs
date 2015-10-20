<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->name('*.php')
    ->in([
        __DIR__ . '/src/',
    ])
;

return Symfony\CS\Config\Config::create()
    ->finder($finder)
    ->fixers(['header_comment', 'short_array_syntax'])
    ->level(\Symfony\CS\FixerInterface::SYMFONY_LEVEL)
;