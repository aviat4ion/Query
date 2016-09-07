<?php

$file_patterns = [
	'src/*.php'
];

if ( ! function_exists('glob_recursive'))
{
	// Does not support flag GLOB_BRACE

	function glob_recursive($pattern, $flags = 0)
	{
		$files = glob($pattern, $flags);

		foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir)
		{
			$files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
		}

		return $files;
	}
}

function get_text_to_replace($tokens)
{
	if ($tokens[0][0] !== T_OPEN_TAG)
	{
		return NULL;
	}

	// If there is already a docblock, as the second token after the
	// open tag, get the contents of that token to replace
	if ($tokens[1][0] === T_DOC_COMMENT)
	{
		return "<?php\n" . $tokens[1][1];
	}
	else if ($tokens[1][0] !== T_DOC_COMMENT)
	{
		return "<?php";
	}
}

function get_tokens($source)
{
	return token_get_all($source);
}

function replace_files(array $files, $template)
{
	foreach ($files as $file)
	{
		$source = file_get_contents($file);
		$tokens = get_tokens($source);
		$text_to_replace = get_text_to_replace($tokens);

		$header = file_get_contents(__DIR__ . $template);
		$new_text = "<?php\n{$header}";

		$new_source = str_replace($text_to_replace, $new_text, $source);
		file_put_contents($file, $new_source);
	}
}

foreach ($file_patterns as $glob)
{
	$files = glob_recursive($glob);
	replace_files($files, '/header_comment.txt');
}

echo "Successfully updated headers \n";