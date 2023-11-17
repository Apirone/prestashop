<?php

$header = <<<TXT
2017-2023 apirone.com

NOTICE OF LICENSE

This source file is subject to the Academic Free License version 3.0
that is bundled with this package in the file LICENSE.md.
It is also available through the world-wide-web at this URL:
https://opensource.org/licenses/AFL-3.0
If you did not receive a copy of the license and are unable to
obtain it through the world-wide-web, please send an email
to license@prestashop.com so we can send you a copy immediately.

@author    Apirone OÜ <support@apirone.com>
@copyright 2017-2023 Apirone OÜ
@license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
TXT;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor']);
                        

$config = new PhpCsFixer\Config();

return $config->setRules(
    [
        '@Symfony' => true,
        'array_indentation' => true,
        'cast_spaces' => [
            'space' => 'single',
        ],
        'combine_consecutive_issets' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'error_suppression' => [
            'mute_deprecation_error' => false,
            'noise_remaining_usages' => false,
            'noise_remaining_usages_exclude' => [],
        ],
        'function_to_constant' => false,
        'method_chaining_indentation' => true,
        'no_alias_functions' => false,
        'no_superfluous_phpdoc_tags' => false,
        'non_printable_character' => [
            'use_escape_sequences_in_strings' => true,
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'phpdoc_summary' => false,
        'protected_to_private' => false,
        'psr_autoloading' => false,
        'self_accessor' => false,
        'yoda_style' => false,
        'single_line_throw' => false,
        'no_alias_language_construct_call' => false,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $header,
            'location' => 'after_open',
            'separate' => 'none',
        ],
    ]
)
    ->setRiskyAllowed(true)
    ->setFinder($finder);
