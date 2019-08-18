<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$fixers = [
    // PHP arrays should be declared using the short syntax.
    'array_syntax' => ['syntax' => 'short'],

    // There MUST be one blank line after the namespace declaration.
    'blank_line_after_namespace' => true,

    // Ensure there is no code on the same line as the PHP open tag and it is followed by a blank line.
    'blank_line_after_opening_tag' => true,

    // An empty line feed should precede a return statement.
    'blank_line_before_return' => true,

    // The body of each structure MUST be enclosed by braces.
    // Braces should be properly placed. Body of braces should be properly indented.
    'braces' => true,

    // A single space should be between cast and variable.
    'cast_spaces' => true,

    // Class, trait and interface elements must be separated with one blank line.
    'class_attributes_separation' => true,

    // Whitespace around the key words of a class, trait or
    // interfaces definition should be one space.
    'class_definition' => true,

    // The keyword elseif should be used instead of else if so that all control keywords look like single words.
    'elseif' => true,

    // PHP code MUST use only UTF-8 without BOM (remove BOM).
    'encoding' => true,

    // PHP code must use the long <?php tags or short-echo <?= tags and not other tag variations.
    'full_opening_tag' => true,

    // Spaces should be properly placed in a function declaration.
    'function_declaration' => true,

    // Add missing space between function’s argument and its typehint.
    'function_typehint_space' => true,

    // Include/Require and file path should be divided with a single space. File path should not be placed under brackets.
    'include' => true,

    // Code MUST use configured indentation type.
    'indentation_type' => true,

    // All PHP files must use same line ending.
    'line_ending' => true,

    // The PHP constants true, false, and null MUST be in lower case.
    'lowercase_constants' => true,

    // PHP keywords MUST be in lower case.
    'lowercase_keywords' => true,

    // In method arguments and method call, there MUST NOT be a space before each comma and there MUST be one space after each comma.
    // Argument lists MAY be split across multiple lines, where each subsequent line is indented once. When doing so,
    // the first item in the list MUST be on the next line, and there MUST be only one argument per line.
    'method_argument_space' => true,

    // Master functions shall be used instead of aliases (http://php.net/manual/ru/aliases.php).
    'no_alias_functions' => true,

    // There should be no empty lines after class opening brace
    'no_blank_lines_after_class_opening' => true,

    // There should not be blank lines between docblock and the documented element.
    'no_blank_lines_after_phpdoc' => true,

    // The closing PHP tag MUST be omitted from files containing only PHP.
    'no_closing_tag' => true,

    // Remove leading slashes in use clauses.
    'no_leading_import_slash' => true,

    // The namespace declaration line shouldn’t contain leading whitespace.
    'no_leading_namespace_whitespace' => true,

    // Single-line whitespace before closing semicolon are prohibited.
    'no_singleline_whitespace_before_semicolons' => true,

    // There MUST NOT be a space after the opening parenthesis.
    // There MUST NOT be a space before the closing parenthesis.
    'no_spaces_inside_parenthesis' => true,

    // Remove trailing commas in list function calls.
    'no_trailing_comma_in_list_call' => true,

    // PHP single-line arrays should not have trailing comma.
    'no_trailing_comma_in_singleline_array' => true,

    // Remove trailing commas in list function calls.
    'no_trailing_whitespace' => true,

    // Unused use statements must be removed
    'no_unused_imports' => true,

    // Remove trailing whitespace at the end of blank lines.
    'no_whitespace_in_blank_line' => true,

    // Logical NOT operators (!) should have leading and trailing whitespaces.
    'not_operator_with_space' => true,

    // Logical NOT operators (!) should have one trailing whitespace.
    'not_operator_with_successor_space' => true,

    // All instances created with new keyword must be followed by braces.
    'new_with_braces' => true,

    // There should not be space before or after object T_OBJECT_OPERATOR ->.
    'object_operator_without_whitespace' => true,

    // Ordering use statements.
    'ordered_imports' => true,

    // Fix phpdoc inline tags, make inheritdoc always inline.
    'phpdoc_indent' => true,

    // Fix PHPDoc inline tags, make inheritdoc always inline.
    'phpdoc_inline_tag' => true,

    // @access annotations should be omitted from phpdocs.
    'phpdoc_no_access' => true,

    // @return void and @return null annotations should be omitted from PHPDoc.
    'phpdoc_no_empty_return' => true,

    // @package and @subpackage annotations should be omitted from phpdocs.
    'phpdoc_no_package' => true,

    // Scalar types should always be written in the same form. int not integer, bool not boolean, float not real or double.
    'phpdoc_scalar' => true,

    // Annotations in phpdocs should be grouped together so that annotations of the same type immediately follow each other,
    // and annotations of a different type are separated by a single blank line.
    'phpdoc_separation' => true,

    // Phpdocs summary should end in either a full stop, exclamation mark, or question mark.
    'phpdoc_summary' => true,

    // Phpdocs should start and end with content, excluding the very first and last line of the docblocks
    'phpdoc_trim' => true,

    // @var and @type annotations should not contain the variable name.
    'phpdoc_var_without_name' => true,

    // Inside class or interface element “self” should be preferred to the class name itself.
    'self_accessor' => true,

    // A PHP file without end tag must always end with a single empty line feed.
    'single_blank_line_at_eof' => true,

    // There should be exactly one blank line before a namespace declaration.
    'single_blank_line_before_namespace' => true,

    // There MUST be one use keyword per declaration.
    'single_import_per_statement' => true,

    // Each namespace use MUST go on its own line and there MUST be one blank line after the use statements block.
    'single_line_after_imports' => true,

    // Convert double quotes to single quotes for simple strings.
    'single_quote' => true,

    // Replace all <> with !=
    'standardize_not_equals' => true,

    // Standardize spaces around ternary operator.
    'ternary_operator_spaces' => true,

    // PHP multi-line arrays should have a trailing comma.
    'trailing_comma_in_multiline_array' => true,

    // Arrays should be formatted like function/method arguments, without leading or trailing single line space.
    'trim_array_spaces' => true,

    // Unary operators should be placed adjacent to their operands.
    'unary_operator_spaces' => true,

    // Visibility MUST be declared on all properties and methods;
    // abstract and final MUST be declared before the visibility;
    // static MUST be declared after the visibility.
    'visibility_required' => true,
];


$finder = Finder::create()->in(['src', 'tests',]);

return Config::create()
    ->setRules($fixers)
    ->setFinder($finder)
    ->setUsingCache(true)
;
