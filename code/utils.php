<?php 
function decode($text)
{
$patterns = array();
$patterns[0] = '/%2B/';
$patterns[1] = '/%20/';
$patterns[2] = '/%3D/';
$replacements = array();
$replacements[2] = '+';
$replacements[1] = ' ';
$replacements[0] = '=';
return preg_replace($patterns, $replacements, $text);
}
?>

