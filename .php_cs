<?php

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers(
        [
            'align_double_arrow',
            'align_equals',
            'multiline_spaces_before_semicolon',
            'ordered_use',
            'short_array_syntax',
            'phpdoc_order',
            '-phpdoc_short_description',
            '-phpdoc_var_without_name',
        ]
    )
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()->in(
            [
                __DIR__ . '/',
            ]
        )
    )
    ->setUsingCache(true);
