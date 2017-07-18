<?php

$start = microtime( true );

include( '../class.paml.php' );

$ctx = new PAML ();

$ctx->force_compile = false;
//$ctx->simple_mode = true;
$ctx->debug = true;
$ctx->use_plugin = false;
//$ctx->caching = false;
//$ctx->cache_driver = 'Memcached';
//$ctx->memcached_servers = [ 'localhost:11211' ];
$ctx->advanced_mode = false;
//$ctx->compile_check = false;
$data = array(
          array('name' => 'John Smith', 'home' => '555-555-5555',
                'cell' => '666-555-5555', 'email' => 'john@myexample.com'),
          array('name' => 'Jack Jones', 'home' => '777-555-5555',
                'cell' => '888-555-5555', 'email' => 'jack@myexample.com'),
          array('name' => 'Jane Munson', 'home' => '000-555-5555',
                'cell' => '123456', 'email' => 'jane@myexample.com')
        );
$ctx->assign('contacts',$data);
$ctx->assign( 'title', 'The title value' );

echo $ctx->build_page( 'tmpl/bench.tpl' );
$end =  microtime(true);
$total = $end - $start;
echo $total;
echo '<br>';
$mem = memory_get_usage();
$mem = number_format($mem);
print("Memory:{$mem}");