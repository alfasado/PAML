<?php
/**
 * PAMLCacheFile : Cache driver for PAML
 *
 * @version    1.0
 * @author     Alfasado Inc. <webmaster@alfasado.jp>
 * @copyright  2017 Alfasado Inc. All Rights Reserved.
 */

class PAMLCacheFile extends PAMLCache {

    protected $compile_dir;
    protected $cache_dir;

    function __construct ( $ctx, $config = array() ) {
        if (! $ctx->compile_dir )
            $ctx->compile_dir = DIR . $this->_compiled . DS;
        if (! $ctx->cache_dir )
            $ctx->cache_dir = DIR . $this->_cache . DS;
        $this->compile_dir = $ctx->compile_dir;
        $this->cache_dir   = $ctx->cache_dir;
    }

    function get ( $key, $ttl = null, $comp = null ) {
        $file = ( strpos( $key, $this->_prefix . 'c__' ) === 0 )
              ? $this->cache_dir . $key . '.php' : $this->compile_dir . $key . '.php';
        if ( file_exists( $file ) ) {
            if (! $ttl ) return file_get_contents( $file );
            $mtime = filemtime( $file );
            if ( ( $comp && ( $mtime < $comp ) )
                || ( ( time() - $mtime ) >= $ttl ) ) {
                unlink( $file );
                return null;
            }
            return file_get_contents( $file );
        }
    }

    function set ( $key, $data, $ttl = null ) {
        $file = ( strpos( $key, $this->_prefix . 'c__' ) === 0 )
              ? $this->cache_dir . $key . '.php' : $this->compile_dir . $key . '.php';
        if ( ! file_exists( $file ) || is_writable( $file ) ) {
            return file_put_contents( $file, $data );
        }
        return false;
    }

    function delete ( $key ) {
        $file = ( strpos( $key, $this->_prefix . 'c__' ) === 0 )
                ? $this->cache_dir . $key : $this->compile_dir . $key . '.php';
        if ( file_exists( $file ) && is_writable( $file ) ) {
            return unlink( $file );
        }
        return false;
    }

    function flush ( $ttl = null, $kind = 'compile_dir' ) {
        if ( $kind === 'both' ) {
            $this->rmtree( $this->compile_dir, $ttl );
            $this->rmtree( $this->cache_dir, $ttl );
        } else {
            $this->rmtree( $this->$kind, $ttl );
        }
    }

    function rmtree ( $dir, $ttl = null ) {
        $_prefix = $this->_prefix;
        $items = scandir( $dir );
        foreach ( $items as $item ) {
            if ( $item == '.' || $item == '..' ) continue;
            $file = $dir . DS . $item;
            if ( is_file( $file ) && file_exists( $file ) && is_writable( $file ) ) {
                if ( strpos( $item, $_prefix ) !== 0 ) continue;
                if (! $ttl ) {
                    unlink( $file );
                } else {
                    $mtime = filemtime( $item );
                    if ( ( time() - $mtime ) >= $ttl ) {
                        unlink( $file );
                    }
                }
            } else if ( is_dir( $file ) ) {
                $this->rmtree( $file, $ttl );
            }
        }
        return;
    }
}