<?php
require_once( '../class.paml.php' );
$paml = new PAML ();
$paml->prefix = 'paml';
$paml->force_compile = true;
$paml->debug = 1;
$paml->plugin_paths[] = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'plugins';
$paml->assign( 'page_title', 'PAML Plugin Example Page' );

$paml->display( 'tmpl/plugin.tpl' );
