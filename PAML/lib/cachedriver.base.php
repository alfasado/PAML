<?php
/**
 * PAMLCache : Base cache driver for PAML
 *
 * @version    1.0
 * @author     Alfasado Inc. <webmaster@alfasado.jp>
 * @copyright  2017 Alfasado Inc. All Rights Reserved.
 */

class PAMLCache {

    protected $driver;
    protected $ctx;
    protected $_compiled = '_compiled';
    protected $_cache    = '_cache';
    public    $_prefix = 'paml__';
    public    $_driver;

    function __construct ( $ctx, $config = array() ) {
        foreach ( $config as $key => $value ) $this->$key = $value;
        if (! $this->driver ) return;
        $driver = $this->driver;
        $class_file = __DIR__ . DS 
            . 'cachedriver.' . strtolower( $driver ) . '.php';
        if ( file_exists( $class_file ) ) {
            require_once( $class_file );
            $_driver = 'PAMLCache' . $driver;
            if (! class_exists ( $_driver ) ) return;
            $cache_driver = new $_driver( $ctx, $config );
            $this->_driver = $cache_driver;
            $this->ctx = $ctx;
            $ctx->cachedriver = $this;
        }
    }

    function get ( $key, $ttl = null, $comp = null ) {
        if (! $this->_driver ) return false;
        return $this->_driver->get( $this->_prefix . $key, $ttl, $comp );
    }

    function getAllKeys () {
        return $this->_driver->getAllKeys();
    }

    function set ( $key, $data, $ttl = null ) {
        if (! $this->_driver ) return false;
        return $this->_driver->set( $this->_prefix . $key, $data, $ttl );
    }

    function delete ( $key, $no_prefix = false ) {
        if (! $this->_driver ) return false;
        return $no_prefix ? $this->_driver->delete( $key )
                          : $this->_driver->delete( $this->_prefix . $key );
    }

    function flush ( $ttl = null ) {
        if (! $this->_driver ) return false;
        return $this->_driver->flush( $ttl );
    }
}