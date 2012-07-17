<?php
 
$finder = Symfony\CS\Finder\DefaultFinder::create()
	->exclude(__DIR__.'/tests/simpletest/')
	->in(__DIR__);

return Symfony\CS\Config\Config::create()
	->fixers(array('linefeed','short_tag','trailing_spaces','php_closing_tag','return','visibility','include','elseif'))
	->finder($finder);
