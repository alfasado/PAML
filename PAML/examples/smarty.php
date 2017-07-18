<?php
require_once( '../class.paml.php' );
$smarty = new PAML ();
$smarty->prefix = '';
$smarty->ldelim = '{';
$smarty->rdelim = '}';
$smarty->timezone = 'Asia/Tokyo';
$smarty->force_compile = true;
$smarty->assign( 'page_title',
                 '<span>PAML Example Page</span>' );
$smarty->assign( array (
                 'foo' => 'foo',
                 'bar' => 'bar',
                 'baz' => 'baz' ) );
$smarty->assign( 'loop_vars1',
                 array ( 'foo','bar','baz', 'qux' ) );
$smarty->assign( 'loop_vars2',
                 array (
                 'foo' => 'bar',
                 'bar' => 'baz',
                 'baz' => 'qux' ) );
$smarty->display( 'tmpl/smarty.tpl' );
