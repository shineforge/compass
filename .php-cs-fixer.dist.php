<?php

/*
 * This file is part of the Compass project, a modern URL manipulation library for PHP.
 * This project was developed with the assistance of generative AI code tools.
 *
 * (c) Shine United LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
	->ignoreDotFiles(false)
	->ignoreVCSIgnored(true)
	->in(__DIR__)
;

return (new Config())
	->setRules([
		'@PHP82Migration' => true,
		'@PHP82Migration:risky' => true,
		'@PER-CS2.0' => true,
		'@PER-CS2.0:risky' => true,
		'align_multiline_comment' => true,
		'backtick_to_shell_exec' => true,
		'binary_operator_spaces' => true,
		'blank_line_before_statement' => true, // need to revisit
		'braces_position' => [
			'allow_single_line_anonymous_functions' => true,
			'allow_single_line_empty_anonymous_classes' => true,
			'classes_opening_brace' => 'same_line',
			'functions_opening_brace' => 'same_line',
			'control_structures_opening_brace' => 'same_line',
		],
		'class_attributes_separation' => [
			'elements' => ['method' => 'one'],
		],
		'class_definition' => [
			'single_line' => true,
			'inline_constructor_arguments' => true,
		],
		'class_reference_name_casing' => true,
		'clean_namespace' => true,
		'combine_consecutive_issets' => true,
		'combine_consecutive_unsets' => true,
		'concat_space' => [
			'spacing' => 'one',
		],
		'declare_parentheses' => true,
		'echo_tag_syntax' => true,
		'empty_loop_body' => [
			'style' => 'braces',
		],
		'empty_loop_condition' => true,
		'explicit_indirect_variable' => true,
		'explicit_string_variable' => true,
		'fully_qualified_strict_types' => [
			'import_symbols' => true,
		],
		'function_declaration' => [
			'closure_fn_spacing' => 'none',
			'closure_function_spacing' => 'none',
		],
		// 'general_phpdoc_tag_rename' => true, // used to rename phpdoc tags
		'global_namespace_import' => [
			'import_classes' => true,
			'import_constants' => true,
			'import_functions' => true,
		],
		'header_comment' => [
			'location' => 'after_open',
			'header' => <<<'EOF'
				This file is part of the Compass project, a modern URL manipulation library for PHP.
				This project was developed with the assistance of generative AI code tools.

				(c) Shine United LLC

				For the full copyright and license information, please view the LICENSE
				file that was distributed with this source code.
				EOF,
		],
		'heredoc_to_nowdoc' => true,
		'include' => true,
		'indentation_type' => true,
		'increment_style' => false,
		'integer_literal_case' => true,
		'lambda_not_used_import' => true,
		'linebreak_after_opening_tag' => true,
		'magic_constant_casing' => true,
		'magic_method_casing' => true,
		'method_argument_space' => true,
		'method_chaining_indentation' => true,
		'multiline_comment_opening_closing' => true,
		'multiline_whitespace_before_semicolons' => [
			'strategy' => 'new_line_for_chained_calls',
		],
		'native_function_casing' => true,
		'native_type_declaration_casing' => true,
		'no_alias_language_construct_call' => true,
		'no_alternative_syntax' => true,
		'no_binary_string' => true,
		'no_blank_lines_after_phpdoc' => true,
		'no_empty_comment' => true,
		'no_empty_phpdoc' => true,
		'no_empty_statement' => true,
		'no_extra_blank_lines' => [
			'tokens' => ['attribute', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'square_brace_block', 'switch', 'throw', 'use'],
		],
		'no_leading_namespace_whitespace' => true,
		'no_mixed_echo_print' => true,
		'no_multiline_whitespace_around_double_arrow' => true,
		'no_null_property_initialization' => true, // this may be contentious, but properties should rely probably on isset rather than a null initialization.
		'no_short_bool_cast' => true,
		'no_singleline_whitespace_before_semicolons' => true,
		'no_spaces_around_offset' => true,
		'no_superfluous_elseif' => true,
		'no_superfluous_phpdoc_tags' => [ // come back to this one
			'allow_hidden_params' => true,
			'remove_inheritdoc' => true, // inheritDoc may not be necssary
		],
		'no_trailing_comma_in_singleline' => true,
		'no_unneeded_braces' => true,
		'no_unneeded_control_parentheses' => [
			'statements' => ['break', 'clone', 'continue', 'echo_print', 'negative_instanceof', 'others', 'return', 'switch_case', 'yield', 'yield_from'],
		],
		'no_unneeded_import_alias' => true,
		'no_unset_cast' => true,
		'no_unused_imports' => true,
		'no_useless_concat_operator' => true,
		'no_useless_else' => true,
		'no_useless_nullsafe_operator' => true,
		'no_whitespace_before_comma_in_array' => true,
		'normalize_index_brace' => true,
		'nullable_type_declaration' => [
			'syntax' => 'question_mark', // alternatively 'union' for |null syntax
		],
		'nullable_type_declaration_for_default_null_value' => true,
		'object_operator_without_whitespace' => true,
		'operator_linebreak' => [
			'only_booleans' => true,
		],
		'ordered_class_elements' => true,
		'ordered_imports' => [
			'imports_order' => ['class', 'function', 'const'],
			'sort_algorithm' => 'alpha',
		],
		'ordered_types' => [
			'null_adjustment' => 'always_last',
			'sort_algorithm' => 'none',
		],
		'php_unit_fqcn_annotation' => false,
		'php_unit_internal_class' => false,
		'php_unit_method_casing' => [
			'case' => 'camel_case',
		],
		'php_unit_test_class_requires_covers' => false,
		'phpdoc_add_missing_param_annotation' => true,
		'phpdoc_align' => true,
		'phpdoc_annotation_without_dot' => true,
		'phpdoc_indent' => true,
		'phpdoc_inline_tag_normalizer' => true,
		'phpdoc_no_access' => true,
		'phpdoc_no_alias_tag' => true,
		'phpdoc_no_empty_return' => true,
		'phpdoc_no_package' => true,
		'phpdoc_no_useless_inheritdoc' => true,
		'phpdoc_order' => [
			'order' => ['param', 'return', 'throws'], // need to test to see if phpstan-* works here
		],
		'phpdoc_order_by_value' => true,
		'phpdoc_return_self_reference' => true,
		'phpdoc_scalar' => true,
		'phpdoc_separation' => [
			'groups' => [
				['author', 'copyright', 'license'],
				['category', 'package', 'subpackage'],
				['property', 'property-read', 'property-write'],
				['deprecated', 'link', 'see', 'since'],
				['phpstan-*'],
			],
		],
		'phpdoc_single_line_var_spacing' => true,
		'phpdoc_summary' => true,
		'phpdoc_tag_type' => [
			'tags' => [
				'api' => 'annotation',
				'author' => 'annotation',
				'copyright' => 'annotation',
				'deprecated' => 'annotation',
				'example' => 'annotation',
				'global' => 'annotation',
				'inheritDoc' => 'inline',
				'internal' => 'annotation',
				'license' => 'annotation',
				'method' => 'annotation',
				'package' => 'annotation',
				'param' => 'annotation',
				'property' => 'annotation',
				'return' => 'annotation',
				'see' => 'annotation',
				'since' => 'annotation',
				'throws' => 'annotation',
				'todo' => 'annotation',
				'uses' => 'annotation',
				'var' => 'annotation',
				'version' => 'annotation',
			],
		],
		'phpdoc_to_comment' => true,
		'phpdoc_trim' => true,
		'phpdoc_trim_consecutive_blank_line_separation' => true,
		'phpdoc_types' => true,
		'phpdoc_types_order' => [
			'null_adjustment' => 'always_last',
			'sort_algorithm' => 'none',
		],
		'phpdoc_var_annotation_correct_order' => true,
		'phpdoc_var_without_name' => true,
		'self_static_accessor' => true,
		'semicolon_after_instruction' => true,
		// 'simple_to_complex_string_variable' => true, // this is a php8.2 thing
		'single_class_element_per_statement' => true,
		'single_import_per_statement' => true,
		'single_line_comment_spacing' => true,
		'single_line_comment_style' => [
			'comment_types' => [
				'asterisk',
				'hash',
			],
		],
		'single_line_empty_body' => true,
		'single_line_throw' => false,
		'single_quote' => true,
		'single_space_around_construct' => true,
		'space_after_semicolon' => [
			'remove_in_empty_for_expressions' => true,
		],
		'standardize_increment' => false,
		'standardize_not_equals' => true,
		'statement_indentation' => true,
		'string_implicit_backslashes' => true,
		'switch_continue_to_break' => true,
		'trailing_comma_in_multiline' => [
			'after_heredoc' => true,
			'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters'],
		],
		'trim_array_spaces' => true,
		'type_declaration_spaces' => true,
		'types_spaces' => true,
		'unary_operator_spaces' => true,
		'whitespace_after_comma_in_array' => [
			'ensure_single_space' => true,
		],
		'yoda_style' => false, // absolutely not
	])
	->setIndent("\t")
	->setFinder($finder)
;
