<?php
/**
 * PAMLCacheMemcached : Cache driver for PAML
 *
 * @version    1.0
 * @author     Alfasado Inc. <webmaster@alfasado.jp>
 * @copyright  2017 Alfasado Inc. All Rights Reserved.
 */

class PAMLCacheMemcached extends PAMLCache {

    protected $instance;

    function __construct ( $ctx, $config = array() ) {
        $servers = $ctx->memcached_servers;
        if (! isset( $servers ) ) {
            $servers[] = 'localhost:11211';
        }
        $memcached = new Memcached();
        foreach ( $servers as $server ) {
            list( $server, $port ) = explode( ':', $server );
            if (! $port ) $port = '11211';
            $memcached->addServer( $server, $port );
        }
        $this->instance = $memcached;
    }

    function get ( $key, $ttl = null, $comp = null ) {
        $cache = $this->instance->get( $key );
        if ( is_array( $cache ) ) {
            list( $mtime, $data ) = $cache;
            if ( $comp && ( $mtime < $comp ) ||
                $ttl && ( time() - $mtime ) >= $ttl ) {
                $this->delete( $key );
                return null;
            }
            return $data;
        }
        return null;
    }

    function getAllKeys () {
        return $this->instance->getAllKeys();
    }

    function set ( $key, $data, $ttl = null ) {
        $this->instance->set( $key, array( time(), $data ) );
        if ( $ttl ) {
            $ttl += time();
            $this->instance->touch( $key, $ttl );
        }
    }

    function delete ( $key, $no_prefix = false ) {
        $this->instance->delete( $key );
    }

    function flush ( $ttl = null ) {
        if (! $ttl ) 
           return $this->instance->flush();
        $keys = $this->instance->getAllKeys();
        if (! is_array( $keys ) ) return false;
        $_prefix = $this->_prefix;
        foreach( $keys as $item ) {
            if ( strpos( $item, $_prefix ) === 0 ) {
                $this->get( $item, $ttl );
            }
        }
        return true;
    }
}