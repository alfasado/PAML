<?php

/**
 * PAML : PHP Alternative Markup Language
 *
 * @version    1.0
 * @package    PAML
 * @author     Alfasado Inc. <webmaster@alfasado.jp>
 * @copyright  2017 Alfasado Inc. All Rights Reserved.
 */
if (! defined( 'DS' ) ) {
    define( 'DS', DIRECTORY_SEPARATOR );
}
if (! defined( 'PAMLDIR' ) ) {
    define( 'PAMLDIR', __DIR__ . DS );
}
if (! defined( 'EP' ) ) {
    define( 'EP', '?>' . PHP_EOL );
}
/**
 * PAMLVSN = Compile format version.
 */
define( 'PAMLVSN', '1.0' );

class PAML {
    private   $version       = 1.0;

/**
 * $prefix        : Tag prefix.
 * $tag_block     : Tag delimiters.
 * $html_block    : Replace HTML tags temporarily.
 * $ldelim,$rdelim: Alias for $tag_block.
 */
    public    $prefix        = 'paml';
    public    $tag_block     = ['{%', '%}'];
    public    $html_block    = ['%[[', ']]%'];
    public    $ldelim, $rdelim;
    public    $html_ldelim, $html_rdelim;
    public    $cache_ttl     = 3600;
    public    $use_plugin    = true;
    public    $caching       = false;
    public    $force_compile = true;
    public    $compile_check = true;
    public    $advanced_mode = true;
    public    $cache_dir;
    public    $compile_dir;
    public    $logging       = false;
    public    $csv_delimiter = ':';
    public    $csv_enclosure = "'";
    public    $plugin_compat = 'smarty_';
    public    $path          = __DIR__;
    public    $esc_trans     = false;
    public    $app           = null;

/**
 * $autoescape: Everything is escaped(When there is no designation of 'raw' modifier).
 */
    public    $autoescape    = false;

/**
 * $debug: 1.error_reporting( E_ALL ) / 2.debugPrint error / 3.debugPrint compiled code.
 */
    public    $debug         = false;

/**
 * $includes: Array of file extensions that allow include.
 */
    public    $includes      =  ['txt', 'tpl', 'tmpl', 'inc', 'html'];

/**
 * $vars           :  Global variables.
 * $__stash['vars']:  Alias for $vars.
 * $local_vars     :  Local variables in block scope.
 * $params         :  Global parameters.
 * $local_params   :  Local parameters in block scope.
 */
    public    $vars          = [];
    public    $__stash       = [];
    public    $local_vars    = [];
    public    $params        = [];
    public    $local_params  = [];

/**
 * $include_paths : Path(s) of php file called $this([path=>true...]).
 * $template_paths: Template or included template path(s)([path=>true...]).
 * $plugin_paths  : Array of plugin directories([path1,path2...]).
 */
    public    $include_paths = [];
    public    $template_paths= [];
    public    $plugin_paths  = [];
    public    $dictionary    = [];

/**
 * $cache_driver = 'Memcached' or null. If null, use simple file cache.
 */
    public    $cache_driver  = null;
    public    $plugin_order  = 0; // 0=asc, 1=desc
    protected $components    = [];
    protected $all_tags      = [];
    protected $ids           = [];
    protected $old_vars      = [];
    protected $old_params    = [];
    protected $func_map      = [];
    protected $block_vars    = [];

    public $default_component= null;

/**
 * $tags: Array of Core Template Tags.
 */
    public   $tags = [
      'block'       => ['block', 'loop', 'foreach', 'for','section', 'literal'],
      'block_once'  => ['ignore', 'setvars', 'capture', 'setvarblock',
                        'assignvars', 'setvartemplate', 'nocache', 'isinchild'],
      'conditional' => ['else', 'elseif', 'if', 'unless', 'ifgetvar', 'elseifgetvar'],
      'modifier'    => ['escape' ,'setvar', 'format_ts', 'zero_pad', 'trim_to', 'eval',
                        'strip_linefeeds', 'sprintf', 'encode_js', 'truncate', 'wrap',
                        'trim_space', 'regex_replace', 'setvartemplate', 'replace',
                        'to_json', 'from_json', 'nocache'],
      'function'    => ['getvar', 'trans', 'setvar', 'property', 'ldelim', 'include',
                        'rdelim', 'fetch', 'var', 'date', 'assign', 'count'],
      'include'     => ['include', 'includeblock', 'extends'] ];
/**
 * $modifier_funcs: Mappings of modifier and PHP functions.
 */
    public    $modifier_funcs = [
      'lower_case'  => 'strtolower', 'upper_case' => 'strtoupper', 'trim' => 'trim',
      'ltrim'       => 'ltrim',  'remove_html'=> 'strip_tags', 'rtrim'=> 'rtrim',
      'nl2br'       => 'nl2br', 'base64_encode' => 'base64_encode' ];
/**
 * $callbacks: Array of Callbacks.
 */
    public    $callbacks = [
      'input_filter'=> [], 'pre_parse_filter'   => [], 'output_filter'=> [],
      'dom_filter'  => [], 'post_compile_filter'=> [] ];

/**
 * Initialize a PAML.
 *
 * @param array $config: Array for set class properties.
 *                          or properties to JSON file.
 */
    function __construct ( $config = [] ) {
        set_error_handler( [ $this, 'errorHandler'] );
        if ( ( $cfg_json = PAMLDIR . 'config.json' ) 
            && file_exists( $cfg_json ) ) $this->configure_from_json( $cfg_json );
        foreach( $config as $k => $v ) $this->$k = $v;
        $this->__stash['vars'] =& $this->vars;
        $this->components['paml'] = $this;
        $this->core_tags = $this->tags;
    }

    function __call( $name, $args ) {
        if (!isset( $this->functions[ $name ] ) ) return;
        return call_user_func_array( $this->functions[ $name ], $args );
    }

    function __get ( $name ) {
        return property_exists( $this, $name ) ? $this->$name : null;
    }

    function init () {
        if ( isset( $this->inited ) ) return;
        if (!$this->language )
            $this->language = substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 );
        if ( $this->debug ) error_reporting( E_ALL );
        $this->tags['modifier'] = array_merge(
            array_keys( $this->modifier_funcs ), $this->tags['modifier'] );
        $tags = $this->tags;
        foreach ( $tags as $kind => $tags_arr )
            $this->all_tags[ $kind ] = array_flip( $tags_arr );
        if ( debug_backtrace()[0] && $f = debug_backtrace()[0]['file'] )
            $this->include_paths[ dirname( $f ) ] = true;
        if (!$this->force_compile && !$this->caching )
            $this->init_cache( $this->cache_driver );
        if ( $this->use_plugin )
      {
        if ( ( $plugin_d = PAMLDIR . 'plugins' ) && is_dir( $plugin_d ) )
            $this->plugin_paths[] = $plugin_d;
        $this->init_plugins();
      }
        if ( $this->ldelim && $this->rdelim )
            $this->tag_block = [ $this->ldelim, $this->rdelim ];
        $this->inited = true;
    }

/**
 * Load plugins.
 */
    function init_plugins () {
        $plugin_paths = $this->plugin_paths;
        foreach ( $plugin_paths as $dir ) {
            $items = scandir( $dir, $this->plugin_order );
            foreach ( $items as $plugin ) {
                if ( strpos( $plugin, '.' ) === 0 ) continue;
                $plugin = $dir . DS . $plugin;
                if ( is_dir( $plugin ) ) {
                    $plugins = scandir( $plugin, $this->plugin_order );
                    foreach ( $plugins as $f ) {
                        if ( ( $_plugin = $plugin . DS . $f ) && ( is_file( $_plugin ) )
                          && ( pathinfo( $_plugin )['extension'] === 'php' ) ) {
                            if (!include( $_plugin ) )
                                trigger_error( "Plugin '{$f}' load failed!" );
                            if ( preg_match ("/^class\.(.*?)\.php$/", $f, $mts ) ) {
                                if (!class_exists( $mts[1] ) ) continue;
                                $obj = new $mts[1]();
                                $registry = property_exists( $obj, 'registry' )
                                          ? $obj->registry : [];
                                $this->register_component(
                                    $obj, dirname( $_plugin ), $registry );
                            }
                        }
                    }
                }
            }
        }
    }

/**
 * Register Smarty2(BC) style plugins.
 */
    function init_functions () {
        $this->functions = [];
        $_pfx = $this->prefix;
        $pfx = preg_quote( $_pfx );
        $plugin_paths = $this->plugin_paths;
        foreach ( $plugin_paths as $dir ) {
            $items = scandir( $dir, $this->plugin_order );
            foreach ( $items as $plugin ) {
                if ( strpos( $plugin, '.' ) === 0 ) continue;
                $plugin = $dir . DS . $plugin;
                if (!preg_match (
                    "/(^.*?)\.(.*?)\.php$/", basename( $plugin ), $mts ) ) continue;
                list( $all, $kind, $tag ) = $mts;
                if ( $kind != 'modifier' ) $tag = preg_replace( "/^$pfx/", '', $tag );
                $this->functions[ "{$kind}_{$tag}" ] =
                    [ $this->plugin_compat . "{$kind}_{$tag}", $plugin ];
                if ( $kind === 'block' ) if ( strpos( $tag, 'if' ) === 0
                    || strpos( $tag, $_pfx . 'if' ) === 0 ) $kind = 'conditional';
                $this->tags[ $kind ][] = $tag;
                $this->all_tags[ $kind ][ $tag ] = true;
            }
        }
    }

/**
 * Set properties from JSON.

 * @param string $json: JSON file path.
 */
    function configure_from_json ( $json ) {
        if (!is_readable( $json ) ) return;
        $config = json_decode( file_get_contents( $json ), true );
        foreach ( $config as $k => $v ) $this->$k = $v;
    }

/**
 * Autoload Smarty2(BC) style modifier function.
 *
 * @param  string $name: Modifier name.
 * @return string $func: Modifier name if function_exists.
 */
    function autoload_modifier ( $name ) {
        $funcs = $this->functions;
        if (!isset( $funcs[ $name ] ) ) {
            $plugin_paths = $this->plugin_paths;
            $f = str_replace( '_', '.', $name ).'.php';
            list( $kind, $tag ) = explode( '_', $name );
            $plugin = '';
            $func = '';
            foreach ( $plugin_paths as $dir ) {
                $f = $dir . DS . $f;
                if ( file_exists( $f ) ) {
                    $plugin = $f;
                    $func = $this->plugin_compat . $name; break;
                }
            }
            if ( $plugin && $func ) $funcs[ $name ] = [ $func, $plugin ];
        }
        if (!isset( $funcs[ $name ] ) ) return null;
        list( $func, $plugin ) = $funcs[ $name ];
        if ( function_exists( $func ) ) return $func;
        $this->functions = $funcs;
        if (!include( $plugin ) ) trigger_error ( "Plugin '$plugin' load failed!" );
        if ( $this->in_nocache ) $this->cache_includes[] = $plugin;
        return function_exists( $func ) ? $func : null;
    }

/**
 * Initialize a cache driver.
 *
 * @param string $driver: 'Memcached' or null.
 */
    function init_cache ( $driver ) {
        if ( $driver ) {
            $class = PAMLDIR . 'lib' . DS . 'cachedriver.base.php';
            if ( file_exists( $class ) && include( $class ) )
                new PAMLCache( $this, ['driver' => $driver ] );
        } else {
            if (!$this->compile_dir ) $this->compile_dir = PAMLDIR . '_compiled' . DS;
            if (!$this->cache_dir ) $this->cache_dir = PAMLDIR . '_cache' . DS;
        }
    }

/**
 * stash: Where the variable is stored.
 *
 * @param  string $name : Name of set or get variable to(from) stash.
 * @param  mixed  $value: Variable for set to stash.
 * @return mixed  $var  : Stored data.
 */
    function stash ( $name, $value = false, $var = null ) {
        if ( isset( $this->__stash[ $name ] ) ) $var = $this->__stash[ $name ];
        if ( $value !== false ) $this->__stash[ $name ] = $value;
        return $var;
    }

/**
 * Set variables.
 *
 * @param  string or array $name : Name of set variables or array variables.
 * @param  array $value          : Variable for set variables.
 */
    function assign ( $name, $value = null ) {
        $assign = !is_array( $name ) ? $this->vars[ $name ] = $value : false;
        if ( $assign === false ) {
            if (!$value ) $value = $name;
            foreach ( $value as $k => $v ) $this->vars[ $k ] = $v;
        }
    }

/**
 * Register plugin component(s).
 *
 * @param  object $obj     : Plugin class object.
 * @param  string $path    : Path of plugin directory.
 * @param  array  $registry: Array of template tags and callbacks.
 *                           Or $registry to file 'config.json'.
 */
    function register_component ( $obj, $path = '', $registry = [] ) {
        $obj->dictionary = [];
        $this->components[ strtolower( get_class( $obj ) ) ] = $obj;
        if ( $path ) $obj->path = $path;
        if ( empty( $registry ) && file_exists( $path . DS . 'config.json' ) )
            $registry = json_decode( file_get_contents( $path . DS . 'config.json' ), 1 );
        foreach ( $registry as $key => $funcs ) {
            if ( $key === 'tags' ) {
                foreach ( $funcs as $kind => $meths ) {
                    $tag_kind = $kind == 'block_once' ? 'block' : $kind;
                    $tags = array_keys( $funcs[ $kind ] );
                    foreach ( $tags as $name ) {
                        $this->tags[ $kind ][] = $name;
                        $this->all_tags[ $kind ][ $name ] = true;
                        $this->func_map[ $tag_kind . '_' . $name ] 
                            = [ $obj, $meths[ $name ] ];
                    }
                }
            } elseif ( $key === 'callbacks' ) {
                foreach ( $funcs as $kind => $meths ) {
                    $callbacks = array_keys( $funcs[ $kind ] );
                    foreach ( $callbacks as $name ) {
                        $method = $meths[ $name ];
                        $this->callbacks[ $kind ][] = $name;
                        $this->func_map[ $name ] = [ $obj, $method ];
                    }
                }
            }
        }
    }

/**
 * You can also register tags respectively.
 */
    function register_tag ( $name, $kind, $method, $obj ) {
        $tag_kind = $kind == 'block_once' ? 'block' : $kind;
        $this->tags[ $kind ][] = $name;
        $this->all_tags[ $kind ][ $name ] = true;
        $this->func_map[ $tag_kind . '_' . $name ] = [ $obj, $method ];
        $this->components[ strtolower( get_class( $obj ) ) ] = $obj;
    }

/**
 * You can also register callbacks respectively.
 */
    function register_callback ( $name, $kind, $method, $obj ) {
        $this->callbacks[ $kind ][] = $name;
        $this->func_map[ $name ] = [ $obj, $method ];
    }

/**
 * Get plugin component.
 *
 * @param  string $name    : Plugin class name.
 * @return object $obj     : Plugin class object.
 */
    function component ( $name ) {
        if ( isset( $this->components[ strtolower( $name ) ] ) )
            return $this->components[ strtolower( $name ) ];
    }

    function component_method ( $name ) {
        if (!isset( $this->func_map[ $name ] ) ) return null;
        list( $obj, $method ) = $this->func_map[ $name ];
        if ( method_exists( $obj, $method ) ) return $this->func_map[ $name ];
    }

/**
 * Do callbacks.
 */
    function call_filter
    ( $res, $type, &$args1 = null, &$args2 = null, &$args3 = null, &$args4 = null ) {
        $args0 = $this;
        $args = $args1;
        $filters = $this->callbacks[ $type ];
        foreach ( $filters as $key )
            if ( list( $obj, $method ) = $this->component_method( $key ) )
                $res = $obj->$method( $res, $args0, $args, $args2, $args3, $args4 );
        return $res;
    }

/**
 * Return a unique string characters not used in the template.
 */
    function magic ( $content = '' ) {
        $magic = '_' . substr( md5( uniqid( mt_rand(), true ) ), 0, 6 );
        if ( isset( $this->ids[ $magic ] ) || strpos( $content, $magic ) !== false )
            return $this->magic( $content );
        if ( $this->ids[ $magic ] = true ) return $magic;
    }

/**
 * Build template file and display or return result.
 *
 * @param   string $path    : Template file path.
 * @param   array  $params  : Array of template variables to set.
 * @param   string $cache_id: Template cache id.
 * @param   bool   $disp    : Display result or return result.
 * @param   string $src     : Template source text.
 * @return  string $content : After processing $content.
 */
    function build_page( $path, $params = [], $cache_id = '', $disp = false, $src = '' ) {
        $force_compile = $this->force_compile;
        $caching = $this->caching;
        if (!$src && ( $path = realpath( $path ) ) && !file_exists( $path ) ) return;
        if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) $this->caching = $caching = false;
        if ( $caching && !$force_compile )
      { // Page cache.
        $this->init_cache( $this->cache_driver );
        if (!$cache_id )
       {
        $req = isset( $_SERVER['HTTP_X_REWRITE_URL'] )
        ? $_SERVER['HTTP_X_REWRITE_URL'] :
        isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : $path;
        $req = $_SERVER['HTTP_HOST'] . $_SERVER['SERVER_PORT'] . $req;
        $cache_id = 'c__' . md5( $req . ':' . $path );
       }
        $this->cache_id = $cache_id;
        $this->cache_path = $this->get_cache( $cache_id, $this->cache_ttl );
        if ( $this->out !== null ) {
            $out = $this->out;
            if ( $disp ) echo $out;
            unset( $this->out, $this->meta );
            return $out;
        }
      }
        $compile_key = '';
        $compile_path = '';
        $this->restore_vars = $this->vars;
        if ( $path )
      {
        $this->include_paths[ dirname( $path ) ] = true;
        $this->template_paths[ $path ] = filemtime( $path );
        $this->template_file = $path;
        $compile_key = md5( $path );
      }
        $this->init();
        $this->re_compile = false;
        if (!$force_compile )
      { // Compile cache.
        $compile_path = $this->get_cache( $compile_key );
        if ( $this->out !== null ) {
            if ( $this->nocache && $caching )
          {
            $this->literal_vars = [];
            $this->re_compile = true;
          } else {
            if (!$caching )
            {
            $out = $this->out;
            if (!empty( $this->callbacks['output_filter'] ) )
                $out = $this->call_filter( $out, 'output_filter' );
            if ( $disp ) echo $out;
            unset( $this->out, $this->meta, $this->old_params, $this->old_vars,
                   $this->template_paths, $this->literal_vars, $this->template_file );
            return $out;
            }
            return $this->finish( null, $disp, $this->callbacks );
          }
        }
      }
        $this->literal_vars = [];
        $this->id = $this->magic();
        $this->compile_path = $compile_path;
        $this->compile_key = $compile_key;
        $content = ( $src ) ? $src : file_get_contents( $path );
        if ( $this->use_plugin && !$this->functions ) $this->init_functions();
        $this->cache_includes = [];
        return $this->compile( $content, $disp, null, null, $params );
    }

/**
 * Do not display result.
 */
    function fetch ( $path, $cache_id = '', $params = [] ) {
        return $this->build_page( $path, $params, $cache_id, false );
    }

/**
 * Display result.
 */
    function display ( $path, $cache_id = '', $params = [] ) {
        return $this->build_page( $path, $params, $cache_id, true );
    }

/**
 * Build template from content.
 */
    function render ( $src, $params = [], $cache_id = '' ) {
        if ( $cache_id ) $this->compile_key = $cache_id;
        return $this->build_page( '', $params, $cache_id, false, $src );
    }

/**
 * Build from source text always it does not use caching.
 *
 * @param  string $src     : Template source text.
 * @param  bool   $compiled: Get compiled PHP code.
 * @return string $build: After processing $src.
 */
    function build ( $src, $compiled = false ) {
        $this->init();
        $old_literal = $this->literal_vars;
        $this->literal_vars = [];
        if ( $this->use_plugin && !$this->functions ) $this->init_functions();
        if (!$this->id ) $this->id = $this->magic();
        $this->in_build = true;
        list( $old1, $old2 ) = [ $this->caching, $this->force_compile ];
        list( $this->caching, $this->force_compile ) = [ false, true ];
        $build = $this->compile( $src, false, null, [], [], $compiled );
        list( $this->caching, $this->force_compile ) = [ $old1, $old2 ];
        $this->in_build = false;
        $this->literal_vars = $old_literal;
        return $build;
    }

/**
 * Quote all values passed to the array.
 *
 * @param   array $values: Array for quote strings.
 * @param   bool  $force : Always quote values(no cache).
 * @return  array $quoted: Array of quoted strings.
 */
    function get_quoted ( $values, $force = false ) {
        if ( $this->quoted_vars && ! $force ) return $this->quoted_vars;
        foreach ( $values as $v ) $quoted[] = preg_quote( $v );
        if (!$force ) $this->quoted_vars = $quoted;
        return $quoted;
    }

/**
 * Search template file from $template_paths and $include_paths.
 *
 * @param  string $path: Path of template file.
 * @return string $path: Real path of specified file.
 */
    function get_template_path ( $path, $continue = false ) {
        $tmpl_paths = array_keys( $this->template_paths );
        $incl_paths = array_keys( $this->include_paths );
        if (!file_exists( $path ) ) {
            foreach ( $tmpl_paths as $tmpl ) {
                $f = dirname( $tmpl ) . DS . $path;
                if ( file_exists( $f ) ) {
                    $path = $f;
                    $continue = true; break;
                }
            }
            if (!$continue ) {
                foreach ( $incl_paths as $tmpl ) {
                    if ( ( $f = $tmpl . DS . $path ) && file_exists( $f ) ) {
                    $path = $f;
                    $continue = true; break;
                    }
                }
            }
        }
        if (!file_exists( $path ) ) return;
        $path = realpath( $path );
        foreach ( $incl_paths as $incl ) {
            if ( strpos( $path, $incl ) === 0 ) {
                $extension = strtolower( pathinfo( $path )['extension'] );
                if (!in_array( $extension, $this->includes ) ) return;
                $this->template_paths[ $path ] = filemtime( $path );
                return $path;
            }
        }
    }

/**
 * Setup tag attributes. value, variable, or array(CSV).
 *
 * @param  array  $args: Array args for setup.
 * @param  string $name: Template tag name.
 * @return array  $args: Set-uped $args.
 */
    function setup_args ( $args, $name = '', $ctx = null, $vars = null ) {
        $string = false;
        if (! $ctx ) $ctx = $this;
        if (! is_array( $args ) ) {
            $args = is_string( $args ) ?
                ['__key__' => $args ] : ['__key__' => (string) $args ];
            $string = true;
        }
        $encl = preg_quote( $ctx->csv_enclosure );
        $delim = preg_quote( $ctx->csv_delimiter );
        foreach ( $args as $k => $v ) {
            if ( strpos( $v, '$' ) === 0 ) { // Variable
                if (!$vars ) $vars = array_merge( $ctx->vars, $ctx->local_vars );
                $v = ltrim( $v, '$' );
                if ( preg_match( "/(.{1,})\[(.*?)]$/", $v, $mts ) )
                    list( $v, $idx ) = [ trim( $mts[1] ), trim( $mts[2] ) ];
                $v = isset( $vars[ $v ] ) ? $vars[ $v ] : '';
                if ( isset( $idx ) ) {
                    $args[ $k ] = isset( $v[ $idx ] ) ? $v[ $idx ] : '';
                    if ( strpos( $idx ,'$' ) === 0 )
                  {
                    $idx = ltrim( $idx, '$' );
                    $idx = isset( $vars[ $idx ] ) ? $vars[ $idx ] : '';
                    if ( is_array( $v ) && isset( $v[ $idx ] ) )
                        $args[ $k ] = ['__array__' => $v[ $idx ] ];
                  }
                } else {
                    $args[ $k ] = $this->setup_args( $v );
                }
            } elseif ( strpos( $v, $delim ) !== false
                && preg_match( "/^{$encl}.*?{$delim}.*{$encl}$/", $v ) ) {
                $arr = $ctx->parse_csv( $v ); // CSV
                $args[ $k ] = $arr;
                if ( strpos( $name, 'regex' ) !== false ) continue;
                $array = [];
                foreach ( $arr as $var )
                    $array[] = strpos( $var ,'$' ) === 0
                        ? $this->setup_args( $var, $name, $ctx, $vars ) : $var;
                $args[ $k ] = $array;
            } elseif ( $k === 'name' && strpos( $v, '.' ) !== false ) { // Array
                if (!$vars ) $vars = array_merge( $ctx->vars, $ctx->local_vars );
                $params = explode( '.', $v );
                $var = array_shift( $params );
                if ( isset( $vars[ $var ] ) && $v = $vars[ $var ] ) {
                    if ( is_array( $v ) ) foreach ( $params as $__key )
                        if ( isset( $v[ $__key ] ) ) $v = $v[ $__key ];
                    if ( isset( $v ) ) $args[ $k ]= ['__array__' => $v ];
                }
            }
        }
        return $string ? $args['__key__'] : $args;
    }

/**
 * Localize variables in block scope.
 *
 * @param array $vars: Array for localize and restore variables names.
 */
    function localize ( $vars = [] ) {
        foreach ( $vars as $var ) {
            if ( is_array( $var ) ) {
                foreach ( $var as $v )
                    if ( isset( $this->__stash['vars'][ $v ] ) )
                        $this->old_vars['vars'][ $v ] = $this->__stash['vars'][ $v ];
            } elseif ( isset( $this->__stash[ $var ] ) ) {
                $this->old_vars[ $var ] = $this->__stash[ $var ];
            }
        }
        $this->restore_vars = $this->vars;
    }

/**
 * Restore variables in block scope.
 *
 * @param array $vars: Array for localize and restore variables names.
 */
    function restore ( $vars = [] ) {
        foreach ( $vars as $var ) {
            if ( is_array( $var ) ) {
                foreach ( $var as $v )
                    $this->__stash['vars'] = isset( $this->old_vars['vars'][ $v ] )
                        ? $this->old_vars['vars'][ $v ] : null;
            } else {
                $this->__stash[ $var ] = isset( $this->old_vars[ $var ] )
                    ? $this->old_vars[ $var ] : null;
            }
        }
        $this->vars = $this->restore_vars;
    }

/**
 * Easily get the value of the variable.
 *
 * @param  string $name : Name of variable.
 * @return string $value: Value of variable.
 */
    function get_any ( $name ) {
        if ( preg_match( "/(.{1,})\[(.*?)]$/", $name, $mts ) )
            list( $name, $idx ) = [ trim( $mts[1] ), trim( $mts[2] ) ];
        $v = isset( $this->local_vars[ $name ] ) ? $this->local_vars[ $name ]
           : ( isset( $this->vars[ $name ] ) ? $this->vars[ $name ] : null );
        if ( isset( $idx ) && is_array( $v ) )
            return isset( $v[ $idx ] ) ? $v[ $idx ] : null;
        if ( isset( $v ) && is_array( $v ) && isset( $v['__eval__'] ) )
            $v = $this->build( $v['__eval__'] );
        return isset( $v ) ? $v : null;
    }

    function do_modifier ( $name, $out, $arg, $ctx ) {
        $func = $this->plugin_compat . $name;
        if (!function_exists( $func ) ) {
            if (!isset( $ctx->functions[ $name ] ) ) return $out;
            $func = $ctx->functions[ $name ][0];
        }
        $arg = $this->setup_args( $arg, $name, $ctx );
        if (!is_array( $arg ) ) $arg = $ctx->parse_csv( $arg );
        array_unshift( $arg, $out );
        $args = self::parse_func( $func, $arg );
        if ( PHP_VERSION >= 5.6 ) return $func( ...$arg );
        for ( $i = 2; $i <= 5 ; $i++ ) {
            $param = 'args' . $i;
            $$param = isset( $args[ $i ] ) ? $args[ $i ] : null;
        }
        return $func( $args[0], $args[1], $args2, $args3, $args4, $args5 );
    }

/**
 * Core template tags.
 */
    function block_block ( $args, $content, $ctx, &$repeat, $counter ) {
        $name = isset( $args['name'] ) ? $args['name'] : null;
        if ( $name && ( $old_var = $ctx->get_any( $name ) ) ) {
            $old_var = $ctx->get_any( $name );
            if ( $old_var !== null ) {
                $repeat = false;
                return $old_var;
            }
        }
        $modifier = $ctx->all_tags['modifier'];
        $modifier['assign'] = true;
        if (!$counter ) {
            $keys = [];
            foreach ( $args as $k => $v )
                if(!isset( $modifier[ $k ] ) ) $keys[] = $k;
            if (!empty( $keys ) ) {
                $ctx->localize( [ $keys, ['block_keys'] ] );
                $ctx->local_vars['block_keys'] = $keys;
                foreach ( $keys as $key ) $ctx->vars[ $key ] = $args[ $key ];
            }
        } else {
            if ( isset( $ctx->local_vars['block_keys'] ) )
                $ctx->restore( [ $ctx->local_vars['block_keys'], ['block_keys'] ] );
        }
        if ( $name && $counter && isset( $ctx->local_vars['__child_context__'] ) ) {
            $append = isset( $args['append'] ) ? $args['append'] : '';
            $prepend = isset( $args['prepend'] ) ? $args['prepend'] : '';
            if ( $append || $prepend ) {
                if (!isset( $ctx->block_vars[ $name ] ) ) $ctx->block_vars[ $name ] = [];
                if ( $append )
              {
                if (!isset( $ctx->block_vars[ $name ]['append'] ) )
                    $ctx->block_vars[ $name ]['append'] = [];
                array_unshift( $ctx->block_vars[ $name ]['append'], $content );
              } elseif ( $prepend ) {
                if (!isset( $ctx->block_vars[ $name ]['prepend'] ) )
                    $ctx->block_vars[ $name ]['prepend'] = [];
                $ctx->block_vars[ $name ]['prepend'][] = $content;
              }
            } else {
                $ctx->vars[ $name ] = $content;
            }
            return;
        }
        if ( $counter && $name && isset( $ctx->block_vars[ $name ] ) ) {
            if ( isset( $ctx->block_vars[ $name ]['append'] ) ) {
                $content .= join( '', $ctx->block_vars[ $name ]['append'] );
            }
            if ( isset( $ctx->block_vars[ $name ]['prepend'] ) ) {
                $content = join( '', $ctx->block_vars[ $name ]['prepend'] ) . $content;
            }
        }
        return $content;
    }

    function block_loop ( $args, &$content, $ctx, &$repeat, $counter, $id ){
        if (!$counter ) {
            if (!isset( $args[ 'name' ] ) && !isset( $args[ 'from' ] ) ) {
                $repeat = false;
                return;
            }
            $from = isset( $args[ 'name' ] ) ? $args[ 'name' ] : $args[ 'from' ];
            $params = is_array( $from ) ? $from : null;
            if (!$params ) $params = isset( $ctx->vars[ $from ] ) ? $ctx->vars[ $from ] : '';
            if (!$params ) $params = isset( $ctx->local_vars[ $from ] ) ? $ctx->local_vars[ $from ] : '';
            if (!$params ) { $repeat = false; return; }
            if ( is_object( $params ) ) $params = (array) $params;
            if (!is_array( $params ) ) return;
            $item = ( isset( $args[ 'item' ] ) ) ? $args[ 'item' ] : '__value__';
            $key  = ( isset( $args[ 'key' ] ) ) ? $args[ 'key' ] : '__key__';
            if ( isset( $params[ 0 ] ) ) {
                if (!is_array( $params[ 0 ] ) ) {
                    $i = 0; foreach ( $params as $param )
                        $arr[] = array( $key => $i++, $item => $param );
                }
            } else {
                foreach( $params as $name => $param )
                    $arr[] = [ $key => $name, $item => $param ];
            }
            if ( isset( $arr ) ) $params = $arr;
            $ctx->local_params = $params;
        }
        if (!isset( $params ) ) $params = $ctx->local_params;
        $ctx->set_loop_vars( $counter, $params );
        $vars = isset( $params[ $counter ] ) ? $params[ $counter ]
              : array_slice( $params, $counter, 1, true );
        if ( $vars ) {
            $repeat = true;
            if ( is_object( $vars ) ) $vars = (array) $vars;
            foreach ( $vars as $key => $value )
                $ctx->local_vars[ $key ] = $value;
        }
        return ( $counter > 1 && isset( $args['glue'] ) )
            ? $args['glue'] . $content : $content;
    }

    function block_for ( $args, $content, $ctx, &$repeat, $counter, $id ) {
        if (!$counter ) {
            if ( isset( $args['start'] ) ) $args['from'] = $args['start'];
            if ( isset( $args['end'] ) ) $args['to'] = $args['end'];
            if ( isset( $args['loop'] ) ) $args['to'] = $args['loop'];
            if ( isset( $args['step'] ) ) $args['increment'] = $args['step'];
            $from = isset( $args['from'] ) ? $args['from'] : 1;
            $to = isset( $args['to'] ) ? $args['to'] : 1;
            $increment = isset( $args['increment'] ) ? $args['increment'] : 1;
            $params = range( $from, $to, $increment );
            $ctx->local_params = $params;
        }
        if (!isset( $params ) ) $params = $ctx->local_params;
        $ctx->set_loop_vars( $counter, $params );
        if ( isset( $params[ $counter ] ) ) {
            $repeat = true;
            $var = isset( $args['var'] ) ? $args['var'] : '__value__';
            $ctx->local_vars[ $var ] = $params[ $counter ];
        }
        return ( $counter > 1 && isset( $args['glue'] ) )
            ? $args['glue'] . $content : $content;
    }

    function block_setvarblock ( $args, &$content, $ctx, &$repeat, $counter ) {
        $name = $args['this_tag'] === 'setvarblock' ? 'name' : 'var';
        if ( isset( $args[ $name ] ) && $args[ $name ] )
            if ( isset( $content ) ) $ctx->vars[ $args[ $name ] ]
                = $ctx->append_prepend( $content, $args, $name );
    }

    function block_setvars ( $args, $content, $ctx, &$repeat, $counter ) {
        $name = ! isset( $args['name'] ) ? '' : $args['name'];
        $lines = array_map( 'ltrim', preg_split( "/\r?\n/", trim( $content ) ) );
        foreach ( $lines as $line ) {
            if ( strpos( $line, '=' ) === false ) continue;
            list( $k, $v ) = preg_split( '/\s*=/', $line, 2 );
            if ( isset( $k ) ) {
                if ( $name ) {
                    $ctx->vars[ $name ][ $k ] = $v;
                } else {
                    $ctx->vars[ $k ] = $v;
                }
            }
        }
    }

    function block_literal ( $args, &$content, $ctx, &$repeat, $counter ) {
        if (!$counter ) return;
        if ( isset( $args['nocache'] ) && ! $ctx->caching ) return $content;
        $var = isset( $ctx->literal_vars[ $args['index'] ] )
             ? $ctx->literal_vars[ $args['index'] ] : '';
        return $var;
    }

    function conditional_if ( $args, $content, $ctx, $repeat, $context = true ) {
        $vars = array_merge( $ctx->vars, $ctx->local_vars );
        list( $true, $false ) = $context ? [ true, false ] : [ false, true ];
        if ( ( isset( $args['test'] ) ) && ( $test = $args['test'] ) ) {
            if ( preg_match_all( "/\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/",
                $test, $mts ) ) {
                foreach ( $mts[0] as $v ) {
                    $variable = preg_replace( '/^\$/', '', $v );
                    $variable = ( isset( $vars[ $variable ] ) ) ? $vars[ $variable ] : '';
                    $test = str_replace( $v, "'{$variable}'", $test );
                }
            }
            $test = str_replace( '"', '', str_replace( '\\', '', $test ) );
            $alloweds = ['int','abs','ceil','cos','exp','floor','log','sin','log10','pi','max'
          ,'min','pow','rand','round','sqrt','srand','tan','strlen','mb_strlen',')','(','=='
          ,'===','!=','!==','<','>','>=','<=','and','or','true','false','{','}','&&','||'];
            foreach ( $alloweds as $allowed ) {
                $result = preg_match( '/[a-z]/', $allowed ) ? ' $1 ' : '$1';
                $allowed = preg_quote( $allowed );
                $test = preg_replace( "/($allowed)/", $result, $test );
            }
            $funcs = strtolower( preg_replace( "/'.*?'/", ' ', $test ) );
            $funcs = preg_split( '/\s{1,}/', $funcs );
            foreach ( $funcs as $func ) {
                if ( $func and ! in_array( $func, $alloweds ) ) {
                    trigger_error( "error in expression '{$test}'" );
                    return $false;
                }
            }
            $test = "return {$test} ? 1 : 0;";
            $result = eval( $test );
            if( $result === false ) trigger_error( "error in expression '{$test}'" );
            return ( $result ) ? $true : $false;
        }
        if (!isset( $args['name'] ) ) return $false;
        if ( strpos( $args['name'], 'request.' ) === 0 ) {
            $v = $ctx->request_var( $args['name'], $args );
        } else {
            if ( isset( $vars[ $args['name'] ] ) ) $v = $vars[ $args['name'] ];
        }
        if ( isset( $v ) && $v ) {
            unset( $args['name'], $args['this_tag'] );
            if ( empty( $args ) ) return $true;
            elseif (is_array( $v ) && !empty( $v ) ) return $true;
            elseif( isset( $args['eq'] ) ) return $v ==$args['eq'] ? $true : $false;
            elseif( isset( $args['not']) ) return $v !=$args['not']? $true : $false;
            elseif( isset( $args['ne'] ) ) return $v !=$args['ne'] ? $true : $false;
            elseif( isset( $args['gt'] ) ) return $v > $args['gt'] ? $true : $false;
            elseif( isset( $args['lt'] ) ) return $v < $args['lt'] ? $true : $false;
            elseif( isset( $args['ge'] ) ) return $v >=$args['ge'] ? $true : $false;
            elseif( isset( $args['le'] ) ) return $v <=$args['le'] ? $true : $false;
            elseif( isset( $args['like'])) return
              strpos( $v, $args['like'] ) !== false ? $true : $false;
            return $true;
        }
        return $false;
    }

    function conditional_unless ( $args, $content, $ctx, $repeat ) {
        return $ctx->conditional_if( $args, $content, $ctx, $repeat, false );
    }

    function _hdlr_if ( $args, $content, $ctx, $repeat, $context ) {
        return $ctx->conditional_if( $args, $content, $ctx, $repeat, $context );
    }

    function conditional_elseif ( $args, $content, $ctx, $repeat, $context ) {
        return $ctx->conditional_if( $args, $content, $ctx, $repeat, $context );
    }

    function conditional_ifgetvar ( $args, $content ) {
        if (!isset( $args['name'] ) ) return false;
        return isset( $this->local_vars[ $args['name'] ] ) &&
            $this->local_vars[ $args['name'] ] ? true :
          ( isset( $this->vars[ $args['name'] ] ) &&
            $this->vars[ $args['name'] ] ? true : false );
    }

    function function_var ( $args, $ctx ) {
        if (!isset( $args['name'] ) ) return;
        if ( isset( $args['value'] ) ) return $ctx->function_setvar( $args, $ctx );
        $name = $args['name'];
        if ( is_array( $name )&&isset( $name['__array__'] ) )
            return $name['__array__'];
        if ( is_string( $name ) && strpos( $name, 'request.' ) === 0 )
            return $ctx->request_var( $args['name'], $args );
        if ( isset( $args['index'] ) ) {
            if ( is_array( $name ) && isset( $name[ $args['index'] ] ) )
                return $name[ $args['index'] ];
            if ( is_string( $name ) ) $name .= '[' . $args['index'] . ']';
        }
        return $ctx->get_any( $name );
    }

    function function_include ( $args, $ctx ) {
        $f = isset( $args['file'] ) ? $args['file'] : '';
        if (! $f ) return '';
        if (!$f = $ctx->get_template_path( $f ) ) return '';
        if (!$incl = file_get_contents( $f ) ) return '';
        return $ctx->build( $incl );
    }

    function function_property ( $args, $ctx ) {
        $prop = $args['name'];
        if ( property_exists( $ctx, $prop ) ) return $ctx->$prop;
    }

    function function_count ( $args, $ctx ) {
        $name = isset( $args['name'] ) ? $args['name'] : '';
        if (!$name ) return 0;
        if ( is_array( $name ) ) return count( $name );
        $v = $ctx->get_any( $name );
        return ( $v ) ? count( $v ) : 0;
    }

    function function_date ( $args, $ctx ) {
        $t = ( isset( $args['ts'] ) && $args['ts'] ) ? strtotime( $args['ts'] ) : time();
        $date = date( 'YmdHis', $t );
        $format = isset( $args['format'] ) ? $args['format'] : 'Y-m-d H:i:s';
        return ( $format ) ? date( $format, strtotime( $date ) ) : $date;
    }

    function function_setvar ( $args, $ctx ) {
        $nm = $args['this_tag'] === 'setvar' ? 'name' : 'var';
        if ( isset( $args[ $nm ] ) && $args[ $nm ] )
            if ( isset( $args['value'] ) )
                $ctx->vars[ $args[ $nm ] ]
                    = $ctx->append_prepend( $args['value'], $args, $nm );
    }

    function function_assign ( $args, $ctx ) {
        return $ctx->function_setvar( $args, $ctx );
    }

    function function_trans ( $args, $ctx ) {
        $phrase = ! isset( $args['phrase'] ) ? '' : $args['phrase'];
        if (!$phrase ) return;
        $component = isset( $args['component'] )
                   ? $ctx->component( $args['component'] ) : $ctx->default_component;
        if (! $component ) $component = $ctx;
        if ( ( $lang = $ctx->language ) && $component ) {
            $dict = isset( $component->dictionary ) ? $component->dictionary : null;
            if ( ( empty( $dict ) || !isset ( $component->dictionary[ $lang ] ) )
            && $path = $component->path ) {
                $locale_dir = $path . DS . 'locale';
                if ( is_dir( $locale_dir ) ) {
                    $locale = $locale_dir . DS . $lang . '.json';
                    if ( file_exists( $locale ) ) $component->dictionary[ $lang ]
                        = json_decode( file_get_contents( $locale ), true );
                }
            }
        }
        if ( $component && ( $dict = $component->dictionary )
            && isset( $dict[ $ctx->language ] )
            && ( $dict = $dict[ $ctx->language ] ) )
            $phrase = isset( $dict[ $phrase ] ) ? $dict[ $phrase ] : $phrase;
        if ( $phrase && ( $params = isset( $args['params'] )
            ? $args['params'] : '' ) ) return ! is_array( $params )
            ? sprintf( $phrase, $params ) : vsprintf( $phrase, $params );
        return $ctx->esc_trans ? htmlspecialchars( $phrase ) : $phrase;
    }

    function function_fetch ( $args, $ctx ) {
        $url = isset( $args['url'] ) ? $args['url'] : '';
        $ua = isset( $args['ua'] ) ? $args['ua'] : 'User-Agent: Mozilla/5.0';
        if (!$url ) return;
        if ( preg_match( '!^https{0,1}://!', $url ) ) {
            $to_encoding = 'UTF-8';
            ini_set( 'user_agent', $ua );
            if ( $contents = file_get_contents( $url ) ) {
              $from_encoding = mb_detect_encoding( $contents, 'UTF-8,EUC-JP,SJIS,JIS' );
              return mb_convert_encoding( $contents, $to_encoding, $from_encoding );
            }
        }
    }

    function function_ldelim ( $args, $ctx ) {
        return $ctx->tag_block[0];
    }

    function function_rdelim ( $args, $ctx ) {
        return $ctx->tag_block[1];
    }

    function modifier_escape ( $str, $arg, $ctx, $name = null ) {
        $arg = strtolower( $arg );
        list( $obj, $metod ) = $ctx->component_method( 'modifier_encode_' . $arg );
        return $obj ? $obj->$metod( $str,1, $ctx, 'modifier_encode_' . $arg ) : $str;
    }

    function modifier_setvar ( $str, $arg, $ctx ) {
        $ctx->vars[ $arg ] = $str;
    }

    function modifier_format_ts ( $date, $format ) {
        return date( $format, strtotime( $date ) );
    }

    function modifier_zero_pad ( $str, $arg ) {
        return sprintf( '%0' . $arg . 's', $str );
    }

    function modifier_strip_linefeeds ( $str, $arg ) {
        return str_replace( ["\r\n", "\n", "\r"], '', $str );
    }

    function modifier_encode_js ( $str, $arg ) {
        $str = json_encode( $str );
        if ( preg_match( '/^"(.*)"$/', $str, $matches ) ) return $matches[1];
    }

    function modifier_sprintf ( $str, $arg ) {
        return sprintf( $arg, $str );
    }

    function modifier_setvartemplate ( $str, $arg, $ctx ) {
        $ctx->vars[ $arg ] = ['__eval__' => $str ];
    }

    function modifier_nocache ( $str, $arg, $ctx ) {
        $this->in_nocache = true;
        $build = $ctx->build( $str, true );
        $this->in_nocache = false;
        return $build;
    }

    function modifier_trim_to ( $str, $arg, $ctx ) {
        return $ctx->modifier_truncate( $str, $arg, $ctx );
    }

    function modifier_truncate ( $str, $len, $ctx ) {
        if ( strpos( $len, $ctx->csv_delimiter )!== false )
            $len = $ctx->parse_csv( $len );
        if ( is_array( $len ) ) {
            $middle = isset( $len[3] ) ? $len[3] : null;
            $break_words = isset( $len[2] ) ? $len[2] : null;
            $tail = isset( $len[1] ) ? $len[1] : null;
            $len = $len[0];
        }
        if ( strpos( $len, '+' ) !== false ) {
            list( $len, $tail ) = explode( '+', $len );
            $plus = true;
        }
        $len = (int) $len;
        if ( $len === 0 ) return;
        if ( mb_strlen( $str ) > $len ) {
            $len -= min( $len, mb_strlen( $tail ) );
            if (!isset( $plus ) && !isset( $break_words ) && !isset( $middle ) )
                $str = preg_replace( '/\s+?(\S+)?$/u', '',
                    mb_substr( $str, 0, $len + 1, 'UTF-8' ) );
            if ( $plus ) $len += mb_strlen( $tail );
            if (!isset( $middle ) ) return mb_substr( $str, 0, $len, 'utf-8' ) . $tail;
            $str = mb_substr( $str, 0, $len / 2, 'utf-8' )
                . $tail . mb_substr( $str, - $len / 2, 'utf-8' );
        }
        return $str;
    }

    function modifier_wrap ( $str, $len ) {
        $len = (int) $len;
        if (!$len ) return $str;
        $cnt = mb_strlen( $str );
        $arr = [];
        for ( $i = 0; $i <= $cnt; $i += $len ) $arr[]
            = mb_substr( $str, $i, $len, 'UTF-8' );
        return join( PHP_EOL, $arr );
    }

    function modifier_from_json ( $json, $name, $ctx ) {
        $json = json_decode( $json, true );
        $ctx->vars[ $name ] = $json;
    }

    function modifier_to_json ( $v ) {
        return json_encode( $v );
    }

    function modifier_eval ( $str, $arg, $ctx ) {
        return ( $arg ) ? $ctx->build( $str ) : $str;
    }

    function modifier_trim_space ( $str, $arg ) {
        if ( $arg == 1 || $arg == 3 ) {
            $ptns = [ ['/^ {2,}/m', ''],['/ +$/m', ''],['/ {2,}/', ' '] ];
            $str = preg_replace(
            array_map( function( $func ) { return $func[0];}, $ptns ),
            array_map( function( $func ) { return $func[1];}, $ptns ), $str );
        }
        if ( $arg == 2 || $arg == 3 ) {
            $list = preg_split( "/[\r\n|\r|\n]/", $str );
            $txt = '';
            foreach ( $list as $out ) if ( $out != '' ) $txt .= $out . PHP_EOL;
            $str = rtrim( $txt );
        }
        return $str;
    }

    function modifier_replace ( $str, $arg, $ctx ) {
        if (!is_array( $arg ) ) $arg = $ctx->parse_csv( $arg );
        return str_replace( $arg[0], $arg[1], $str );
    }

    function modifier_regex_replace ( $str, $args, $ctx ) {
        if (!is_array( $args ) ) $args = $ctx->parse_csv( $args );
        $i = 0;
        foreach ( $args as $arg ) {
            if ( ( $pos = strpos( $arg, "\0" ) ) !== false ) {
                $arg = substr( $arg, 0, $pos );
                if ( preg_match( '!([a-zA-Z\s]+)$!s', $arg, $match )
                   && ( strpos( $match[1], 'e' ) !== false ) ) {
                     $arg = substr( $arg, 0, -strlen( $match[1] ) )
                          . preg_replace( '![e\s]+!', '', $match[1] );
                }
            }
            $args[ $i ] = $arg;
            $i += 1;
        }
        return preg_replace( $args[0], $args[1], $str );
        return $str;
    }

/**
 * Get from predefined variables $_REQUEST.
 */
    function request_var ( $name, $args ) {
        $name = preg_replace( "/request\./", '', $name );
        if (!isset( $_REQUEST[ $name ] ) ) return;
        $var = $_REQUEST[ $name ];
        if ( isset( $args['setvar'] ) ) return $var;
        return is_array( $var ) ? array_values( $var )[0] : $var;
    }

/**
 * Specified append or prepend attribute for setvar(block) or assign(block).

 * @param  string $str : Content for append or prepend.
 * @param  array  $args: Tag Attributes.
 * @param  string $name: Name of variables.
 * @return string $str : After processing $content.
 */
    function append_prepend ( $str, $args, $name ) {
        if ( $v = $this->get_any( $args[ $name ] ) ) {
            if ( isset( $args['append'] ) && $args['append'] ) {
                return $v . $str;
            } elseif ( isset( $args['prepend'] ) && $args['prepend'] ) {
                $str .= $v;
            }
        }
        return $str;
    }

/**
 * Auto set reserved loop variables.
 *
 * @param   int   $cnt   : Loop counter.
 * @param   array $params: Array or object for loop.
 */
    function set_loop_vars ( $cnt, $params ) {
        $this->local_vars[ '__first__' ] = $cnt === 0;
        $this->local_vars[ '__last__' ] = !isset( $params[ $cnt + 1 ] );
        $even = $cnt % 2;
        $this->local_vars[ '__even__' ] = $even;
        $this->local_vars[ '__odd__' ] = !$even;
        $this->local_vars[ '__index__' ] = $cnt;
        $this->local_vars[ '__counter__' ] = $cnt + 1;
        if ( $cnt===0 ) $this->local_vars[ '__total__' ] = count( $params );
    }

    function parse_csv ( $s ) {
    return str_getcsv( stripslashes( $s ), $this->csv_delimiter, $this->csv_enclosure );
    }

/**
 * Parse tag literal, setvartemplate and nocache to array $this->literal_vars,
 *  and convert to literal tag
 *
 * @param string $content : Template source.
 * @param int    $kind    : null(literal), 1(setvartemplate), 2(nocache).
 * @return bool : nocache tag exists or not.
 */
    function parse_literal ( &$content, $kind = null ) {
        list( $tag_s, $tag_e, $h_sta, $h_end, $pfx ) = $this->quoted_vars;
        if (!$kind  ) $tagname = 'literal';
        else $tagname = $kind === 1 ? 'setvartemplate' : 'nocache';
        $regex = "/(($tag_s|<)$pfx:{0,1}{$tagname}.*?($tag_e|>))(.*?)"
               . "(($tag_s|<)\/$pfx:{0,1}{$tagname}($tag_e|>))/is";
        if (!preg_match_all( $regex, $content, $mts ) ) return false;
        $count = count( $mts[0] );
        for ( $i = 0; $i < $count; $i++ ) {
            $block = $mts[4][ $i ];
            $tag = preg_quote( $mts[0][ $i ] );
            $cnt = (string) count( $this->literal_vars );
            $idx = " index=\"{$cnt}\"";
            if ( $kind ) $block = str_replace( $this->insert_text, '', $block );
            if (!$kind ) {
                $start = str_replace( 'literal', 'literal' . $idx,
                    strtolower( $mts[1][ $i ] ) );
                $end = $mts[5][ $i ];
            } elseif ( $kind === 1 ) {
                $name = ( preg_match( '/name="(.*?)"/', $mts[1][ $i ], $attr ) )
                      ? $attr[1] : '';
                $name = addslashes( $name );
                $start = preg_replace( "/setvartemplate/i", 'literal setvartemplate="'
                       . $name . '"' . $idx, $mts[1][ $i ], 1 );
                $end = str_replace( 'setvartemplate', 'literal', $mts[5][ $i ] );
            } else {
                $start = preg_replace( "/nocache/i", 'literal nocache="1"' . $idx,
                    $mts[1][ $i ], 1 );
                $end = str_replace( 'nocache', 'literal', $mts[5][ $i ] );
            }
            $content = preg_replace( "!$tag!si", $start . $block . $end, $content, 1 );
            $this->literal_vars[] = $block;
        }
        return ( $kind === 2 ) ? true : false;
    }

/**
 * Template compiler.
 *
 * @param string $content : Template source.
 * @param bool   $disp    : Display result or return result.
 * @param array  $tags_arr: Array of all template tags.
 * @param array  $params  : Array of template variables.
 * @param bool   $compiled: Return compiled PHP code.
 * @param bool   $nocache : Whether the nocache tag is used or not.
 * @return string $out    : After processing $content(or compiled PHP code).
 */
    function compile ( $content, $disp = true, $tags_arr = null, $use_tags = [],
      $params = [], $compiled = false, $nocache = false ) {
        if (!$this->build_start ) {
            $this->tags['block'] = array_unique(
                array_merge( $this->tags['block'], $this->tags['block_once'] ) );
            $magic = $this->magic( $content );
            $this->html_block = [ '%' . $magic, $magic . '%' ];
            list( $this->html_ldelim, $this->html_rdelim ) = 
                array( '==' . $magic, $magic . '==' );
        }
        $this->build_start = true;
        $requires = [];
        foreach ( $params as $k => $v ) $this->vars[ $k ] = $v;
        list( $literals, $templates ) = (!$this->_include )
            ? [ $this->literal_vars, $this->template_paths ] : [ [], [] ];
        $callbacks = $this->callbacks;
        $all_tags = $this->all_tags;
        if (!$this->allow_php ) $content = self::strip_php( $content );
        if (!empty( $callbacks['input_filter'] ) )
            $content = $this->call_filter( $content, 'input_filter' );
        $dom = new DomDocument();
        $id = $this->magic( $content );
        $in_nocache = $this->in_nocache;
        $tags = $this->tags;
        $prefix = $this->prefix;
        if (!$tags_arr )
      { // Process in descending order of length of the tag names.
        $tags_arr = array_merge( $tags['block'], $tags['function'],
            $tags['conditional'], $tags['include'] );
        usort( $tags_arr, create_function('$a,$b','return strlen($b)-strlen($a);') );
      }
        list( $t_sta, $t_end, $sta_h, $end_h )
            = array_merge( $this->tag_block, $this->html_block );
        list( $tag_s, $tag_e, $h_sta, $h_end, $pfx )
            = $this->get_quoted( [ $t_sta, $t_end, $sta_h, $end_h, $prefix ] );
        if (!$this->_include ) if ( stripos( $content, 'literal' ) !== false )
            $this->parse_literal( $content );
        if (!$pfx && ( $pfx = $prefix = 'paml' ) )
            $content = preg_replace( '/' . $tag_s . '(\/{0,1})\${0,1}(.*?)\${0,1}' .
            $tag_e . '/si', $t_sta . '$1paml:$2' . $t_end, $content );
        if ( strpos( $t_sta ,'<' ) === 0 && strpos( $t_end ,'>' ) !== false )
      {
        $content = preg_replace( '/' . $tag_s . '(\/{0,1})\${0,1}(' . $pfx .
            '.*?)\${0,1}' . $tag_e . '/si', '{%$1$2%}', $content );
        list( $t_sta, $t_end, $tag_s, $tag_e ) = ['{%', '%}', '\{%', '%\}'];
      }
        $content = preg_replace( '/' . $tag_s . '\${0,1}(' .
        $pfx . '.*?)\${0,1}' . $tag_e . '/si', '<$1>', $content );
        $content = preg_replace('/' . $tag_s . '(\/' . $pfx . '.*?)' .
        $tag_e . '/si', '<$1>', $content );
        foreach ( $tags_arr as $tag )
      {
        $close = isset( $all_tags['function'][ $tag ] ) || $tag === 'include'
            || $tag === 'extends' || $tag === 'else' || $tag === 'elseif'
            || $tag === 'elseifgetvar' ? ' /' : '';
        $content = preg_replace("/<\\s*\\" . "\${0,1}{$pfx}:{0,1}\s*{$tag}(.*?)\\\${0,1}>"
        . '/si', $t_sta . $pfx . $tag . '$1' . $close . $t_end, $content );
        $content = preg_replace( "!<\\s*{$pfx}:{0,1}\s*{$tag}(.*?)(>)!si", $t_sta . $pfx
        . $tag . '$1' . $close . $t_end, $content );
        $content = preg_replace( "!<\/{$pfx}:{0,1}\s*{$tag}\s*?>!si", $t_sta . '/' . $pfx
        . $tag . '$1' . $t_end, $content );
      }
        $content = preg_replace( '/<([^<|>]*?)>/s', $sta_h . '$1' . $end_h, $content );
        // <> in JavaScript
        $content = str_replace( '<', $this->html_ldelim, $content );
        $content = str_replace( '>', $this->html_rdelim, $content );
        $content = "<{$id}>{$content}</{$id}>";
        $content = preg_replace( '/' . $tag_s . '(\/{0,1}' . $pfx . '.*?)'
        . $tag_e . '/si', '<$1>', $content );
        if ( stripos( $content, 'setvartemplate' ) !== false )
            $this->parse_literal( $content, 1 );
        if ( stripos( $content, 'nocache' ) !== false ) {
            $res = $this->parse_literal( $content, 2 );
            if (!$nocache ) $nocache = $res;
        }
     // Measures against the problem that blank characters disappear.
        $insert = $this->insert_text ? $this->insert_text : "__{$id}__";
        $content = preg_replace( "/(<\/{0,1}$pfx.*?>)/",
        $insert . '$1' . $insert, $content );
     // Double escape HTML entities.
        $content = preg_replace_callback( "/(&#{0,1}[a-zA-Z0-9]{2,};)/is",
        function( $mts ) { return htmlspecialchars( $mts[0], ENT_QUOTES ); }, $content );
        if (!empty( $callbacks['pre_parse_filter'] ) )
        $content = $this->call_filter( $content, 'pre_parse_filter', $insert );
        libxml_use_internal_errors( true ); // Tag parsing.
        if (!$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES','utf-8' ),
            LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD|LIBXML_COMPACT ) )
            trigger_error( 'loadHTML failed!' );
        $this->_include = false;
        $include_tags = $tags['include'];
        foreach ( $include_tags as $include )
    {
        $elements = $dom->getElementsByTagName( $prefix . $include );
        if (!$elements->length ) continue;
        $i = $elements->length - 1;
        $use_tags[] = 'include_' . $include;
        while ( $i > -1 )
      {
        $ele = $elements->item( $i );
        $i -= 1;
        if ( $f = $ele->getAttribute( 'file' ) )
        {
        if ( strpos( $f, '$' ) !== false ) continue;
        if (!$f = $this->get_template_path( $f ) ) continue;
        if (!$incl = file_get_contents( $f ) ) continue;
        if ( stripos( $incl, 'literal' ) !== false ) $this->parse_literal( $incl );
        list( $attrs, $t_args, $attributes ) = $this->get_attributes( $ele );
        unset( $attrs['file'] );
        $this->_include = true;
        if ( $include === 'includeblock' )
      {
        $nodeValue = str_replace( $insert, '', $ele->nodeValue );
        $attributes .= ' contents="' . addslashes( $nodeValue ) . '"';
      }
        if (!empty( $attrs ) )
            $incl="<{$prefix}block {$attributes}>{$incl}</{$prefix}block>";
        $parent = $ele->parentNode;
        if ( $include === 'extends' )
          {
            $parent->appendChild( $dom->createTextNode("</{$prefix}isinchild>". $incl ) );
            $parent->replaceChild( $dom->createTextNode("<{$prefix}isinchild>"), $ele );
          } else {
            $parent->replaceChild( $dom->createTextNode( $incl ), $ele );
          }
        }
      }
    }
        if ( $this->_include )
      { // Processed recursively if included.
        $out = mb_convert_encoding( $dom->saveHTML(), 'utf-8', 'HTML-ENTITIES' );
        $out = preg_replace( "!^.*?<$id>(.*?)<\/$id>.*$!si", '$1', $out );
        return $this->compile( str_replace( $insert, '', $out ),
            $disp, $tags_arr, $use_tags, [], $compiled, $nocache );
      }
        $pid = '$' . $this->id . '_';
        $adv = $this->advanced_mode;
        $modifier_funcs = $this->modifier_funcs;
        $functions = $this->functions;
        $modifiers = $all_tags['modifier'];
        $esc = $this->autoescape;
        $func_map = $this->func_map;
        $block_tags = $tags['block'];
        $core_tags = $this->core_tags;
        $cores = $core_tags['block'];
        $core_once  = $core_tags['block_once'];
        foreach ( $block_tags as $block )
    {
        if ( $block === 'setvartemplate' || $block === 'nocache' ) continue;
        $elements = $dom->getElementsByTagName( $prefix . $block );
        if (!$elements->length ) continue;
        if ( $block === 'capture' ) $block = 'setvarblock';
        elseif ( $block === 'foreach' ) $block = 'loop';
        elseif ( $block === 'section' ) $block = 'for';
        elseif ( $block === 'assignvars' ) $block = 'setvars';
        $i = $elements->length - 1;
        $tag_name = 'block_' . $block;
        $use_tags[] = $tag_name;
        $method = $p = isset( $functions[ $tag_name ] ) ? $functions[ $tag_name ][0] : '';
        if ( $method )
      {
        if (!function_exists(!$method ) ) include( $functions[ $tag_name ][1] );
        if ( $in_nocache ) $this->cache_includes[] = $functions[ $tag_name ][1];
      }
        if ( isset( $func_map[ $tag_name ] ) )
      {
        list( $class, $name ) = $this->func_map[ $tag_name ];
        $method = '$this->component(\'' . get_class( $class ) . '\')->' . $name;
      } elseif ( in_array( $block, $cores ) || in_array( $block, $core_once ) )
        $method = "\$this->{$tag_name}";
      {
      }
        if (!$method ) continue;
        while ( $i > -1 )
      {
        $ele = $elements->item( $i );
        $i -= 1;
        $bid = $this->magic( $content );
        if ( $block === 'isinchild' )
      {
        $sta = "<?php {$pid}local_vars['__child_context__']=true;ob_start();?>";
        $end = "<?php unset({$pid}local_vars['__child_context__']);ob_end_clean();?>";
      } else
      {
        $restore = "{$pid}local_params={$pid}old_params['{$bid}'];"
                 . "{$pid}local_vars={$pid}old_vars['{$bid}'];" . EP;
        list ( $_args, $_content, $_repeat, $_params, $_param ) =
            ['a' . $bid, 'c' . $bid, 'r' . $bid, 'ps' . $bid, 'p' . $bid ];
        list( $attrs, $t_args ) = $this->get_attributes( $ele, $block, $p );
        $out = $this->add_modifier( "\${$_content}",
            $attrs, $modifiers, $modifier_funcs, $func_map, $requires, false );
        list( $begin, $last ) = strpos( $out, '(' )
            ? ['ob_start();', "\${$_content}=ob_get_clean();echo({$out});"] : ['', ''];
        $setup_args = $adv ? "\$this->setup_args({$t_args},null,\$this)" : $t_args;
        $sta = "<?php \${$_content}=null;{$begin}{$pid}old_params['{$bid}']="
             . "{$pid}local_params;{$pid}old_vars['{$bid}']={$pid}local_vars;"
             . "\${$_args}={$setup_args};";
        if ( isset( $all_tags['block_once'][ $block ] ) )
       {
        $sta .= "ob_start();" . EP;
        $end ="<?php \${$_content}=ob_get_clean();\${$_content}=$method(\${$_args},"
             . "\${$_content},\$this,\${$_repeat},1,'{$bid}');echo({$out});{$restore}";
       } else
       {
        $cond = "while(\${$_repeat}===true):";
        $sta .= "\${$bid}=-1;\${$_repeat}=true;${cond}\${$_repeat}=(\${$bid}!==-1)"
             .  "?false:true;echo $method(\${$_args},\${$_content},"
             .  "\$this,\${$_repeat},++\${$bid},'{$bid}');ob_start();" . EP;;
        $end = "<?php \${$_content}=ob_get_clean();endwhile;{$last}{$restore}";
       }
      }
        $parent = $ele->parentNode;
        if ( $block === 'ignore' )
        {
          $parent->removeChild( $ele );
        } else {
          if ( $block === 'literal' ) $ele->nodeValue = '';
          $parent->insertBefore( $dom->createTextNode( $sta ), $ele );
          $parent->insertBefore( $dom->createTextNode( $end ), $ele->nextSibling );
        }
      }
    }
        $cores = $core_tags['conditional'];
        $conditional_tags = $tags['conditional'];
        foreach ( $conditional_tags as $conditional )
    {
        $elements = $dom->getElementsByTagName( $prefix . $conditional );
        if (!$elements->length ) continue;
        $i = $elements->length - 1;
        $tag_name = 'conditional_' . $conditional;
        $use_tags[] = $tag_name;
        $method = $p = isset( $functions[ $tag_name ] )? $functions[ $tag_name ][0] : '';
        if ( $method )
      {
        if (!function_exists(!$method ) ) include( $functions[ $tag_name ][1] );
        if ( $in_nocache ) $this->cache_includes[] = $functions[ $tag_name ][1];
      }
        if ( isset( $func_map[ $tag_name ] ) )
      {
        list( $class, $name ) = $this->func_map[ $tag_name ];
        $method = '$this->component(\'' . get_class( $class ) . '\')->' . $name;
      } elseif ( in_array( $conditional , $cores ) ) {
        $method = $conditional === 'elseifgetvar' ? '$this->conditional_ifgetvar'
                : '$this->conditional_' . $conditional;
      }
        if (!$method ) continue;
        while ( $i > -1 )
      {
        $ele = $elements->item( $i );
        $i -= 1;
        list( $attrs, $t_args ) = $this->get_attributes( $ele, $conditional, $p );
        $bid = $this->magic( $content );
        $_args = '_' . $bid;
        $out = $this->add_modifier( "\${$bid}",
            $attrs, $modifiers, $modifier_funcs, $func_map, $requires, false );
        list( $begin, $last ) = strpos( $out, '(' )
            ? ['ob_start();', "\${$bid}=ob_get_clean();echo {$out};"] : ['', ''];
        $setup_args = $adv ? "\$this->setup_args({$t_args},null,\$this)" : $t_args;
        if (!$adv && ( $conditional === 'ifgetvar' || $conditional === 'elseifgetvar' )
            && isset( $attrs['name'] ) ) {
            $nm = $attrs['name'];
            $cond = "(isset({$pid}local_vars['${nm}'])&&{$pid}local_vars['${nm}'])||"
                  . "(isset({$pid}vars['${nm}'])&&{$pid}vars['${nm}'])";
        } else {
            $cond = "{$method}({$setup_args},null,\$this,true,true)";
        }
        $parent = $ele->parentNode;
        if ( $conditional === 'elseif' || $conditional === 'elseifgetvar' )
        {
          $parent->replaceChild( 
              $dom->createTextNode( '<?php elseif(' . $cond . '):' . EP ), $ele );
        } elseif ( $conditional === 'else' ) {
          $parent->replaceChild( $dom->createTextNode('<?php else:' . EP ), $ele );
        } else {
          $sta = "<?php $begin{$pid}old_params['{$bid}']={$pid}local_params;"
               . "{$pid}old_vars['{$bid}']={$pid}local_vars;if({$cond}):" . EP;
          $end = "<?php endif;{$last}{$pid}local_params={$pid}old_params['{$bid}'];"
               . "{$pid}local_vars={$pid}old_vars['{$bid}'];" . EP;
          $parent->insertBefore( $dom->createTextNode( $sta ), $ele );
          $parent->insertBefore( $dom->createTextNode( $end ), $ele->nextSibling );
        }
      }
    }
        $function_tags = $tags['function'];
        $cores = $core_tags['function'];
        foreach ( $function_tags as $function )
    {
        $elements = $dom->getElementsByTagName( $prefix . $function );
        if (!$elements->length ) continue;
        $i = $elements->length - 1;
        $tag_name = 'function_' . $function;
        $use_tags[] = $tag_name;
        $method = $p = isset( $functions[ $tag_name ] ) ? $functions[ $tag_name ][0] : '';
        if ( $method )
      {
        if (!function_exists(!$method ) ) include( $functions[ $tag_name ][1] );
        if ( $in_nocache ) $this->cache_includes[] = $functions[ $tag_name ][1];
      }
        if ( isset( $func_map[ $tag_name ] ) )
      {
        list( $class, $name ) = $this->func_map[ $tag_name ];
        $method = '$this->component(\'' . get_class( $class ) . '\')->' . $name;
      } elseif ( in_array( $function, $cores ) ) {
        $method = "\$this->{$tag_name}";
      }
        if (!$method ) continue;
        while ( $i > -1 )
      {
        $ele = $elements->item( $i );
        $i -= 1;
        list( $attrs, $t_args ) = $this->get_attributes( $ele, $function, $p );
        if (!$adv && $function === 'var' ) $function = 'getvar';
        if ( $function === 'getvar' ) {
            $nm = addslashes( $attrs['name'] );
            $out = "isset({$pid}local_vars['{$nm}'])?{$pid}local_vars['{$nm}']:"
                 . "(isset({$pid}vars['{$nm}'])?{$pid}vars['{$nm}']:'')";
        } else {
            $setup_args = $adv ? "\$this->setup_args({$t_args},null,\$this)" : $t_args;
            $out = "{$method}({$setup_args},\$this)";
        }
        $out = '<?php echo ' . $this->add_modifier( $out, $attrs, $modifiers,
            $modifier_funcs, $func_map, $requires, $esc ) . EP;
        $ele->parentNode->replaceChild( $dom->createTextNode( $out ), $ele );
      }
    }
    if (!empty( $callbacks['dom_filter'] ) )
        $dom = $this->call_filter( $dom, 'dom_filter' );
    $out = mb_convert_encoding( $dom->saveHTML(), 'utf-8', 'HTML-ENTITIES' );
    unset( $dom, $content );
    $out = str_replace( $this->html_ldelim, '<', $out );
    $out = str_replace( $this->html_rdelim, '>', $out );
    $out = str_replace( $insert, '', $out );
    if ( preg_match_all( "/{$h_sta}\\s*\\\${0,1}({$pfx}|\\\$):{0,1}\\s"
        . "*\\\${0,1}(.*?)\\\${0,1}{$h_end}/is", $out, $mts ) )
      { // Convert inline variables to 'var' tag.
        list( $xml, $tag_cnt, $matches ) = [ new DOMDocument(), -1, $mts[2] ];
        foreach ( $matches as $tag )
        {
          list( $v, $tag, $attrs ) = [ null, trim( $tag ), [] ];
          if ( preg_match( "/(.{1,})\[.*?]$/", $tag, $_mts ) ) {
              list( $v, $tag ) = [ $tag, trim( $_mts[2] ) ];
        } elseif ( strpos( $tag, '.' ) !== false ) {
          $parse_tag = preg_split( "/\s{1,}/", $tag );
          if ( strpos( $parse_tag[0], '.' ) !== false )
              list( $v, $tag ) = [ $parse_tag[0], 'dummy'];
        }
          $src = '<?xml version="1.0" encoding="UTF-8"?><root><' . $tag . ' /></root>';
          if (!$xml->loadXML( $src ) ) continue;
          $_tag = $xml->getElementsByTagName( 'root' )->item( 0 )->firstChild;
          list( $_attrs, $nm ) = [ $_tag->attributes, $_tag->tagName ];
          if (!preg_match( "/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $nm ) )
              continue;
          if ( isset( $v ) ) $nm = $v;
          for ( $i = 0; $i < $_attrs->length; $i++ )
              $attrs[ $_attrs->item( $i )->name ] = $_attrs->item( $i )->value;
          $res = $adv ? "\$this->function_var(\$this->setup_args(['name'=>'{$nm}'],null,"
               . "\$this),\$this)" : "isset({$pid}local_vars['{$nm}'])?{$pid}local_vars"
               . "['{$nm}']:(isset({$pid}vars['{$nm}'])?{$pid}vars['{$nm}']:'')";
          if ( isset( $attrs ) && !empty( $attrs ) )
              $res = $this->add_modifier( $res, $attrs, $modifiers, $modifier_funcs,
                  $func_map, $requires, $esc );
          $out = str_replace( $mts[0][++$tag_cnt ], "<?php echo {$res}" . EP, $out );
        } unset( $xml );
      }
        if (!empty( $callbacks['post_compile_filter'] ) )
            $out = $this->call_filter( $out, 'post_compile_filter' );
        $out = str_replace( ["<{$id}>", "</{$id}>"], '', $out );
        $out = preg_replace( '/' . $h_sta . '(.*?)' . $h_end . '/si', '<$1>', $out );
        $out = preg_replace( "/<\/{0,1}{$pfx}.*?>/si", '', $out );
        if ( $compiled ) return $out;
        $_pfx = $this->id . '_';
        $vars = "<?php \${$_pfx}vars=&\$this->vars;\${$_pfx}old_params=&\$this->"
              . "old_params;\${$_pfx}local_params=&\$this->local_params;\${$_pfx}"
              . "old_vars=&\$this->old_vars;\${$_pfx}local_vars=&\$this->local_vars;?>";
        $out = $vars . $out;
        $require = '';
        if (!$this->in_build && !$this->force_compile && $this->compile_key )
      {
        foreach ( $use_tags as $func )
            if ( isset( $functions[ $func ] ) )
                $require .= "include('" . $functions[ $func ][1] . "');";
        if (!empty( $requires ) )
      {
        $requires = array_keys( $requires );
        foreach ( $requires as $path ) $require .= "include('{$path}');";
      }
        if (!$this->re_compile ) $this->set_cache( $this->compile_key, $out,
            $this->compile_path, $require, $nocache );
        $this->nocache = false;
      }
        return $this->finish( $out, $disp, $callbacks, $literals, $templates, $nocache );
    }

/**
 * Set(Get) cache.
 */
    function set_cache ( $key, $out, $path = null, $req = '', $nocache = false ) {
        $meta = '$this->meta=' . var_export( [ 'template_paths' => $this->template_paths,
        'version' => PAMLVSN, 'advanced' => $this->advanced_mode ], true ) . ';';
        $meta .= ( $nocache ) ? '$this->nocache=true;' : '';
        $meta .= '$this->literal_vars=' . var_export( $this->literal_vars, true ) . ';';
        $out ="<?php {$req}{$meta}ob_start();?>{$out}<?php \$this->out=ob_get_clean();?>";
        $this->cachedriver
            ? $this->cachedriver->set( $key, $out ) : file_put_contents( $path, $out );
    }

    function get_cache ( $key, $ttl = null, $comp = null, $path = null ) {
        if (!$this->compile_check ) $ttl = null;
        $this->out = null;
        $this->meta = null;
        if ( $this->cachedriver ) {
            $out = $this->cachedriver->get( $key, $ttl, $comp );
            if ( isset( $out ) ) $out = $this->_eval( $out );
        } else {
            $cdir = strpos( $key, 'c__' ) === 0 ? $this->cache_dir : $this->compile_dir;
            $path = $cdir . $this->prefix . '__' . $key . '.php';
            if ( file_exists( $path ) )
                if (!$ttl || ( time() - filemtime( $path ) < $ttl ) ) include( $path );
        }
        if ( $meta = $this->meta ) {
            if (!$this->compile_check ) return $path;
            if ( $meta['version'] !== PAMLVSN ||
                $meta['advanced'] !== $this->advanced_mode ) {
                $this->out = null;
                $this->meta = null;
                return $path;
            }
            $tpl_paths = $meta['template_paths'];
            foreach( $tpl_paths as $tmpl => $mod ) {
                if (!file_exists( $tmpl ) || filemtime( $tmpl ) > $mod ) {
                    $no_cache = true;
                    break;
                }
            }
            if (!isset( $no_cache ) ) return $path;
        }
        $this->out = null;
        $this->meta = null;
        return $path;
    }

/**
 * DOMElement attributes to PHP code or PAML template attributes.

 * @param  object $elem  : Object DOMElement.
 * @param  string $tag   : Template tag name.
 * @param  string $plugin: Plugin's method.
 * @return array( array, string, string ) $arguments: Set-uped $arguments.
 */
    function get_attributes ( $elem, $tag = null, $plugin = null ) {
        list( $_attrs, $attributes, $attrs, $t_args ) = [ $elem->attributes, '', [], [] ];
        if ( $tag && !$plugin ) $elem->setAttribute( 'this_tag', $tag );
        $length = $_attrs->length;
        for ( $i = 0; $i < $length; $i++ ) {
            $attr_n = strtolower( addslashes( $_attrs->item( $i )->name ) );
            if ( $attr_n === 'assign' ) $attr_n = 'setvar';
            $attr_v = addslashes( $_attrs->item( $i )->value );
            $t_args[] = "'{$attr_n}'=>'{$attr_v}'";
            $attrs[ $attr_n ] = $attr_v;
            $attributes .= " {$attr_n}=\"{$attr_v}\"";
        }
        return [ $attrs, '[' . join( ',', $t_args ) . ']', $attributes ];
    }

/**
 * Recursively interpose output with a modifiers.
 *
 * @param  string $out       : Output variable.
 * @param  array  $attributes: Tag attributes.
 * @param  array  $modifiers : All modifiers.
 * @param  array  $modifier_funcs: Mapping of modifier name and function name.
 * @param  array  $func_map  : Mapping of function and [ $plugin, $method ].
 * @param  array  $requires  : Plug-ins required to load.
 * @param  bool   $esc       : Need escape or not.
 * @return string $out       : PHP code for output.
 */
    function add_modifier
      ( $out, $attributes, $modifiers, $modifier_funcs, $func_map, &$requires, $esc ) {
        foreach ( $attributes as $attr_n => $attr_v ) {
            $attr_v = addslashes( $attr_v );
            if (!isset( $modifiers[ $attr_n ] ) ) continue;
            if ( $attr_n === 'escape' && ( strtolower( $attr_v ) === 'html' ||
                strtolower( $attr_v ) === 'url' || $attr_v == 1 || !$attr_v ) ) {
                $out = strtolower( $attr_v ) === 'url' ?
               'rawurlencode(' . $out . ')':'htmlspecialchars(' . $out . ',ENT_QUOTES)';
            } elseif ( isset( $modifier_funcs[ $attr_n ] ) ) {
                $out = $modifier_funcs[ $attr_n ] . '(' . $out . ')';
            } else {
                if ( method_exists( $this, 'modifier_' . $attr_n ) ) {
                    $out = "\$this->modifier_{$attr_n}({$out},\$this->setup_args"
                         ."('{$attr_v}','{$attr_n}',\$this),\$this,'{$attr_n}')";
                } else {
                    $mname = 'modifier_' . addslashes( $attr_n );
                    if (isset( $func_map[ $mname ] ) ) {
                        list( $class, $name ) = $func_map[ $mname ];
                        $method = '$this->component(\''
                                . get_class( $class ) . '\')->' . $name;
                        $out = "{$method}({$out},\$this->setup_args('{$attr_v}',"
                             . "'{$attr_n}',\$this),\$this,'{$attr_n}')";
                    } else {
                        $f = $this->autoload_modifier( $mname );
                        $requires[ $this->functions[ $mname ][1] ] = true;
                        $out = "\$this->do_modifier('{$mname}',{$out},'{$attr_v}',\$this)";
                    }
                }
            }
        }
        if ( $esc && (!isset( $attributes['raw'] ) || !$attributes['raw'] ) )
            $out = "htmlspecialchars({$out},ENT_QUOTES)";
        return $out;
    }

/**
 * Finalize. Display content or return content.
 */
    function finish ( $out, $disp, $cb, $lits = [], $tmpls = [], $nocache = false ) {
        if ( $this->debug === 3 ) $this->debugPrint( $out );
        $out = $this->out ? $this->out : $this->_eval( $out );
        if (!empty( $cb['output_filter'] ) )
            $out = $this->call_filter( $out, 'output_filter' );
        if ( $this->caching && $this->cache_id ) {
            $require = '';
            $includes = $this->cache_includes;
            $includes = array_unique( $includes );
            foreach ( $includes as $include ) $require .= "include('{$include}');";
            $this->set_cache( $this->cache_id, $out, $this->cache_path, $require );
            $this->cache_includes = [];
        }
        if ( $nocache ) $out = $this->_eval( $out );
        $this->literal_vars = $lits;
        $this->template_paths = $tmpls;
        if (!$this->in_build ) unset( $this->out );
        if ( $disp ) echo $out;
        return $out;
    }

/**
 * Strip PHP tags from a input content.
 */
    static function strip_php ( $php ) {
        list( $tokens, $res, $in_php ) = [ token_get_all( $php ), '', false ];
        foreach ( $tokens as $token ) {
            list( $id, $str ) = is_string( $token ) ? ['', $token ] : $token;
            if (!$in_php ) {
                $in_php = $id === T_OPEN_TAG || $id === T_OPEN_TAG_WITH_ECHO;
                if ( $in_php === false ) $res .= $str;
            } else {
                if ( $id === T_CLOSE_TAG ) $in_php = false;
            }
        }
        return $php !== $res ? self::strip_php( $res ) : $res;
    }

/**
 * Parse Smarty2(BC) style modifier plugin.
 */
    static function parse_func ( $func, $params, $args = [] ) {
        $ref = new ReflectionFunction( $func );
        $info = preg_replace( '/\/\*.*?\*\//s', '', $ref->export( $func, true ) );
        if (!preg_match_all( "/.*?Parameter\s*?\#.*?\>\s*?(\\$.*?)\s*?\]/",
            $info, $mts ) ) return $params;
        $i = 0;
        foreach ( $mts[1] as $param ) {
            $param = trim( $param );
            $item = isset( $params[ $i ] ) ? $params[ $i ] : null;
            if ( strpos( $param, '=' ) !== false &&
                list( $left, $right ) = preg_split( "/\s*=\s*/", $param ) ) {
                $right = strtolower( $right );
                switch ( true ) {
                    case ctype_digit( $right ):
                      $args[] = ( $item ) ? (int) $item : $right; break;
                    case preg_match( '/^[\'"](.*?)[\'"]$/', $right, $mts ):
                      $args[] = ( $item ) ? "{$item}" : $mts[1]; break;
                    case $right === 'true' || $right === 'false':
                      $item = ( $item ) ? strtolower( $item ) : $right;
                      $args[] = $item != 'false' && $item!='0' && ( $item != '' )
                        ? true : false; break;
                    case $right === 'null':
                      $item = isset( $item ) ? $item : null;
                      $args[] = $item; break;
                    default:
                      $args[] = $item;
                }
            } else {
                $args[] = $item;
            }
            ++$i;
        }
        return $args;
    }

    function _eval ( $out ) {
        ob_start();eval( '?>' . $out . '<?' ); $out = ob_get_clean();
        if ( $err = error_get_last() )
        $this->errorHandler( $err['type'], $err['message'], $err['file'], $err['line'] );
        return $out;
    }

    function debugPrint ( $msg ) {
        echo '<hr><pre>', htmlspecialchars( $msg ), '</pre><hr>';
    }

    function errorHandler ( $errno, $errmsg, $f, $line ) {
        if ( $tmpl = $this->template_file ) $errmsg = " $errmsg( in {$tmpl} )";
        $msg = "{$errmsg} ({$errno}) occured( line {$line} of {$f} ).";
        if ( $this->debug === 2 ) $this->debugPrint( $msg );
        if ( $this->logging && !$this->log_path ) $this->log_path = PAMLDIR . 'log' . DS;
        if ( $this->logging ) error_log( date( 'Y-m-d H:i:s T', time() ) .
            "\t" . $msg . "\n", 3, $this->log_path . 'error.log' );
    }
}