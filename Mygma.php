<?php

# What you need to do:
# - change the $pygmentize_path to your pygments path
# - add Mygma.php to your extensions folder
# - add a require_once in LocalSettings.php
# - Add CSS settings in MediaWiki:Common.css
# - Enjoy

$pygmentize_path = '/usr/bin/pygmentize';

$wgExtensionCredits['parserhook']['Mygma'] = array(
	'path'           => __FILE__,
	'name'           => 'Mygma',
	'author'         => array( 'Ola Bini' ),
	'description'    => 'Provides syntax highlighting using Pygments',
	'descriptionmsg' => 'syntaxhighlight-desc',
);

if ( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
	$wgHooks['ParserFirstCallInit'][] = 'MygmaParserInit';
} else {
	$wgExtensionFunctions[] = 'MygmaParserInit';
}

function MygmaParserInit() {
	global $wgParser;
	$wgParser->setHook( 'source', 'MygmaRender' );
	return true;
}

function MygmaRender( $input, $args = array(), $parser ) {
    global $pygmentize_path;
    if( isset( $args['lang'] ) && $args['lang'] ) {
        $lang = '-l ' . escapeshellarg($args['lang']) . ' ';
    } else {
        $lang = '';
    }

    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
        );

    $cwd = '/tmp';
    $process = proc_open($pygmentize_path . ' ' . $lang . '-O encoding=utf8 -f html', $descriptorspec, $pipes, $cwd, NULL);
    if (is_resource($process)) {
        fwrite($pipes[0], $input);
        fclose($pipes[0]);
        $result = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($process);
        return $result;
    }
    return "FAILURE";
}
