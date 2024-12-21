<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        'single_line_empty_body' => false,
        'global_namespace_import' => true,
        'yoda_style' => false,
        'class_attributes_separation' => false,
        'concat_space' => false,
        'nullable_type_declaration_for_default_null_value' => true,
        'method_argument_space' => false,
    ])
    ->setFinder($finder)
;
