<?php
require_once( '../class.paml.php' );
$ctx = new PAML ();
$ctx->prefix = 'tmpl_';
$ctx->timezone = 'Asia/Tokyo';
$ctx->force_compile = true;
$ctx->assign( 'page_title', '<span>PAML Example Page</span>' );
$ctx->assign( array (
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => 'baz' )
             );
$ctx->assign( 'loop_vars1',
                  array ( 'foo','bar','baz', 'qux' ) );
$ctx->assign( 'loop_vars2',
                  array ( 'foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux' ) );
$ctx->display( 'tmpl/html.tpl' );
