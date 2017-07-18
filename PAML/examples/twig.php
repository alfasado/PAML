<?php
require_once( '../class.paml.php' );
$twig = new PAML ();
$twig->prefix = '';
$twig->ldelim = '{{';
$twig->rdelim = '}}';
$twig->timezone = 'Asia/Tokyo';
$twig->force_compile = true;
$twig->include_paths[ __DIR__ ] = true;
$tpl = 'tmpl/twig.tpl';
$twig->template_paths[ $tpl ] = filemtime( $tpl );
$cache_id = md5( realpath( $tpl ) );
$tpl = file_get_contents( $tpl );
$tpl = preg_replace( '/\{%\s*end(.*?)\s*%\}/i', '{{/$1}}', $tpl );
$tpl = preg_replace( '/\{%\s*(.*?)?\s*%\}/', '{{$1}}', $tpl );
$params = array(
    'page_title' => '<span>PAML Example Page</span>',
    'foo' => 'foo',
    'bar' => 'bar',
    'baz' => 'baz',
    'loop_vars1' => array ( 'foo','bar','baz', 'qux' ),
    'loop_vars2' => array ( 'foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux' ),
);
echo $twig->render( $tpl, $params, $cache_id );
