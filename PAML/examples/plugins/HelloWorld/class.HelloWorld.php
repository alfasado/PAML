<?php
/**
 * PAMLPluginHelloWorld = Example Plugin for PAML
 *
 * @version    0.1
 * @author     Alfasado Inc. <webmaster@alfasado.jp>
 * @copyright  2017 Alfasado Inc. All Rights Reserved.
 */

class HelloWorld {

    public $version = 0.1;

    /**
     * An array of tags and callbacks.
     * It can also be written in 'config.json'.
     */
    /*
    public $registry = array (
        'tags' => array (
            'function' => array(
                'hello' => 'plugin_hello',
            ),
            'block' => array(
                'ppap' => 'plugin_ppap',
            ),
            'modifier' => array(
                'add' => 'plugin_add',
            ),
        ),
    );
    */

    /**
     * Implementation for function tag.
     */
    function plugin_hello ( $args, $ctx ) {
        $value = isset( $args[ 'value' ] ) ? $args[ 'value' ] : 'World!';
        return "Hello ${value}";
    }

    /**
     * Implementation for block tag.
     */
    function plugin_ppap ( $args, &$content, $ctx, &$repeat, $counter ) {
        if (! isset( $content ) ) {
            $ctx->localize( array( $ctx->common_loop_vars ) );
            $ctx->stash(
                'ppap_arr', [ 'pen', 'apple', 'pineapple', 'pen-pineapple-apple-pen' ] );
        }
        $ppap_arr = $ctx->stash( 'ppap_arr' );
        $ctx->set_loop_vars( $counter, $ppap_arr );
        if ( isset( $ppap_arr[ $counter ] ) ) {
            $ctx->vars[ '__value__' ] = $ppap_arr[ $counter ];
            $repeat = true;
        } else {
            $ctx->restore( array( $ctx->common_loop_vars ) );
            $repeat = false;
        }
        return $content;
    }
    
    function plugin_add ( $str, $arg, $ctx ){
        return $str . $arg;
    }
}

?>