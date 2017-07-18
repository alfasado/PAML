<?php
require_once( '../class.paml.php' );
$ctx = new PAML ();
$ctx->prefix = 'mt';
$ctx->timezone = 'Asia/Tokyo';
$ctx->force_compile = true;
$ctx->debug = true;
$vars =& $ctx->__stash[ 'vars' ];
$vars[ 'page_title' ] = '<span>PAML Example Page</span>';
$vars[ 'foo' ] = 'foo';
$vars[ 'bar' ] = 'bar';
$vars[ 'baz' ] = 'baz';
$vars[ 'loop_vars1' ] = array ( 'foo','bar','baz', 'qux' );
$vars[ 'loop_vars2' ] = array (
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'baz' => 'qux' );
echo $ctx->build_page( 'tmpl/mt.tpl' );
