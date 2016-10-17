<?php 
require 'vendor/autoload.php';
use League\HTMLToMarkdown\HtmlConverter;

$converter = new HtmlConverter();

$html = "<h3>Quick, to the Batpoles!</h3>";
$markdown = $converter->convert($html);
echo $markdown;