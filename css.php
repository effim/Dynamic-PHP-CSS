<?

/**
 * @author Corey Ward
 * @version 0.01
 * @copyright Effim LLC., 18 July, 2009
 **/

/**
 * Dynamic PHP CSS
 **/


// ini_set('display_errors', 'on');

$url = isset($_GET['style']) ? $_GET['style'] : false;
if (!$url) trigger_error('No CSS file specified. Exiting.') && exit;

// @todo: Sanitize URL
// @todo: Select most appropriate method of 'getting' stylesheet.

$fh = fopen($url, 'r');
$css = null;

while (!feof($fh)) {
	$css .= fread($fh, 4096);
}
fclose($fh);

// strip excess whitespace
$css = str_replace("\t", '', $css); // excess tabs
$css = preg_replace('/$[\r\n\t ]+^/m', "\n", $css); // empty lines
$css = preg_replace('/ +([;:])/', '\1', $css); // space before/after colons and semicolons
$css = preg_replace('/: \$/', ':$', $css); // space between colons and variables

// find vars
preg_match_all('/(\$[a-zA-Z0-9_-]{1,}) ?= ?([^;]+);/', $css, $vars);

$count = count($vars[1]);
for ($i=0; $i<$count; $i++) {
	$key = $vars[1][$i];
	$val = $vars[2][$i];
	
	// if the value is set to '-' we try to grab the info from the query string
	if ($val == '-') {
		$val = isset($_GET[str_replace('$', '', $key)]) ? $_GET[str_replace('$', '', $key)] : '';
	}
	
	$styleVars[$key] = $val;
}

// strip var declarations
$css = preg_replace('/\$[a-zA-Z0-9_-]{1,} ?= ?[^;]+;[\r\n]*/', '', $css);

// replace vars in css
foreach ($styleVars as $k => $v) {
	$css = preg_replace('/(?<=:)' . '\\' . $k . '(?=;)/', $v, $css);
}


header('Content-type: text/css');
print $css;

?>