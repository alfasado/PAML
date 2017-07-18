<?php
$start = microtime(true);

require_once( '../class.paml.php' );
$ctx = new PAML ();

$ctx->debug  = 1;
$ctx->prefix = 'mt';
$ctx->csv_delimiter = ',';
$ctx->var_prefix = '__';
$ctx->var_postfix = '__';
$ctx->timezone = 'Asia/Tokyo';
$ctx->plugin_paths[] = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'plugins';

$ctx->force_compile = false;
//$ctx->cache_driver = 'Memcached';
$ctx->caching = true;
//$ctx->cache_driver = 'File'; // or $ctx->cache_driver = 'Memcached';
// $ctx->trim_output  = true;
// $ctx->allow_php = false;

// $ctx->vars = array( 'key' => 'value', ... );
// echo $ctx->build( '<mt:Date>' );

$vars =& $ctx->__stash[ 'vars' ];
$arr = array (
                'mobile_os_title' => 'Mobile OS',
                'cms_title'       => 'CMS',
                'ppap_title'      => '<PPAP>',
                'plugin_title'    => 'Plugin'
              );
foreach ( $arr as $key => $value ) {
    $vars[ $key ] = $value;
}
$vars[ 'page_title' ] = 'JAML Example Page';
$vars[ 'ppap_loop' ] = array (
                'pen', 'apple', 'pineapple', 'pen-pineapple-apple-pen' );
$vars[ 'mobile_os_loop' ] = array (
                'Microsoft' => 'Windows Mobile',
                'Google'    => 'Android',
                'Apple'     => 'iOS',
              );
$vars[ 'cms_loop' ] = array (
                array( 'name' => 'PowerCMS', 'company' => 'Alfasado' ),
                array( 'name' => 'WordPress', 'company' => 'Automattic' ),
                array( 'name' => 'Mobavle Type', 'company' => 'SixApart' )
              );

echo $ctx->build_page( 'tmpl/template-like-mt.tmpl' );
$end =  microtime(true);

$total = $end - $start;
echo $total;
