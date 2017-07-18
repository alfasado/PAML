# PAML : PHP Alternative Markup Language

* version    1\.0
* author     Alfasado Inc\. &lt;webmaster@alfasado\.jp&gt;
* copyright  2017 Alfasado Inc\. All Rights Reserved\.

## System Requirements

* PHP version requirements to PHP 5\.6 or 7 or later\.
* Supports UTF\-8 encoded text only\.

## Synopsis

### paml.php

    <?php
        require_once( '../class.PAML.php' );
        $ctx = new PAML ();
        $ctx->prefix = 'paml';
        $ctx->assign( 'page_title', '<span>PAML Example Page</span>' );
        $ctx->assign( array (
                        'foo' => 'bar',
                        'bar' => 'baz',
                        'baz' => 'qux' )
                     );
        $ctx->assign( array( 'loop_vars1' =>
                          array ( 'foo','bar','baz', 'qux' )
                    ) );
        $ctx->assign( array (
                        'loop_vars2' =>
                           array (
                            'foo' => 'bar',
                            'bar' => 'baz',
                            'baz' => 'qux' )
                    ) );
        $ctx->display( 'tmpl/template.tpl' );

### template.tpl

    <!DOCTYPE html>
    <html>
    <head>
    <title><paml:var name="page_title" remove_html="1"></title>
    </head>
    <body>
    <h1><paml:var name="page_title"></h1>

    <p><paml:var name="foo">,<paml:var name="bar">,<paml:var name="baz"></p>

    <paml:loop from="$loop_vars1">
    <paml:if name="__first__">
      <ul>
    </paml:if>
        <li class="<paml:if name="__odd__">odd<paml:else>even</paml:if>">
            <paml:var name="__value__" escape="1">
        </li>
    <paml:if name="__last__">
      </ul>
    </paml:if>
    </paml:loop>

    <paml:loop from="$loop_vars2" sort_by="value:reverse">
    <paml:if name="__first__">
      <ul>
    </paml:if>
        <li class="<paml:if name="__odd__">odd<paml:else>even</paml:if>">
            <paml:var name="__key__"> : <paml:var name="__value__" escape="1">
        </li>
    <paml:if name="__last__">
      </ul>
    </paml:if>
    </paml:loop>

## Markup notation

Template Tags are not case sensitive\. You can give '$' at
the beginning and ending\.
You can give ':' at after prefix\. The closing tag '/' is optional\.

In modifiers that do not require argument, arguments is optional\.

### Function Tags with attribute and modifier

    <pamlvar name="name" escape>, <pamlvar name="name" escape="1">,
    <PAMLVar name="name" escape />, <paml:getVar name="name" escape="1" />,
    <Paml:var name="name" escape="1" />, <$paml:Getvar name="name" escape="1"$>,
    <paml:Var name="name" escape="1" />...

### Inline Variable

    <paml:setvar name="name" value="value">
    <paml:name> is equivalent to <paml:var name="name">
        or
    <paml:array.key1.key2>

The following template does not work well because Template tags are high priority than inline variable.

    <paml:setvar name="var" value="value">
    <paml:$var> <= OK
    <paml:var>  <= It is interpreted as a template tag var.

    <paml:setvar name="variable" value="value">
    <paml:$variable> <= OK
    <paml:variable>  <= It is also interpreted as a template tag var.
    
In order to avoid this, it is better to give a prefix (e.g.'_') to the beginning of the variable name or explicitly give $ to the beginning.

### Block Tags with attribute and modifier

    <paml:block param1="1" param2="2"> ... </paml:block>

### Conditional Tags with attribute and modifier

    <paml:if name="variable_name1"> 
        ...
    <paml:elseif name="variable_name2">
        ...
    <paml:else>
        ...
    </paml:if>

### Modifier and Tag attributes

The tag arguments specification is made by either value, array with key, variable, or array\(CSV\)\.

    value    : <paml:var name="name">
    array    : <paml:var name="array[key]">
          or : <paml:var name="array.key1.key2">
    variable : <paml:var name="$variable">
          or : <paml:var name="$variable[key]">
    request variable 
             : <paml:var name="request.name">
                            (From HTTP Request variables '$_REQUEST'.)
    csv      : <paml:var name="name" replace="'value1':'value\'2'">
          or : <paml:var name="name" replace="value1:value2">
                            (<=Depending on the tag implementation.)

    CSV enclosure and CSV delimiter are properties of the class(they can be changed).

        Initial value
             : $csv_enclosure = "'" (enclosure)
               $csv_delimiter = ':' (delimiter)

## Template Tags

### Function Tags

### var

A function tag used to store and return values from variables\. if given 'value' attribute,
set a variable \(no output when setting the value of a variable\) \(Alias for 'setvar'\)\.

#### attributes

* name\(required\) : A name for get or set variable.  
* value            : For set a variable.  
* append           : Append a value to the end of an existing variable.  
* prepend          : Prepend a value to the end of an existing variable.

### getvar

Alias for 'var'\. When it's confirmed that name is a literal, please specify this\(faster\)\.

### setvar

A function tag used to set the value of a template variable.

#### attributes

* name\(required\) : A name for set variable\.
* var              : Alias for 'name'\.
* value            : For set a variable\.
* append           : Append a value to the end of an existing variable\.
* prepend          : Prepend a value to the end of an existing variable\.

### assign

Alias for 'setvar'.

### trans

Translate phrase\. See =&gt; \./locale/ja\.json

#### attributes

* phrase    : String for translate.
* params    : For format string(s). use sprintf function. When passing multiple values, specify CSV.
* component : Plugin class name.

### ldelim

Output left\_delimiter\(Default '{{'\)\.

### rdelim

Output left\_delimiter\(Default '}}'\)\.

## Block Tags

### block

Execute the loop only once. If a name attribute is specified and the variable has a value, output it.

#### attributes
* name    : Set a output value to variable\. Output empty string\.
* append  : The block content will be be appended to the content of the parent template block\.
* prepend : The block content will be be prepended to the content of the parent template block\.
* The other attributes be passed to the inside of block as custom attribute\.

### loop

Loop over the values in a hash or an array.

#### attributes

* name\(required\) : The name of the array, hash data to process\.
* from             : Alias for 'name'
* key              : The name of the variable that is the current key\.
* item             : The name of the variable that is the current element\.
* sort\_by         : Causes the data in the given array to be resorted in the manner specified\. e\.g\.: sort\_by="value,numeric,reverse"
* glue             : When specified, the string will be placed in between each row\.

#### reserved variables

Within this tag, the following variables are assigned

* \_\_first\_\_   : Assigned when the loop is in the first iteration\.
* \_\_last\_\_    : Assigned when the loop is in the last iteration\.
* \_\_odd\_\_     : Assigned true when the loop is on odd numbered rows\.
* \_\_even\_\_    : Assigned true when the loop is on even numbered rows\.
* \_\_counter\_\_ : Loop counter\.
* \_\_index\_\_   : Loop counter\(Starting from 0\)\.
* \_\_key\_\_     : Holds the 'key' of the array or objects for loop\(When the tag attribute 'key' is not specified\.\)\.
* \_\_value\_\_   : Holds the 'value' of the array or objects for loop \(When the tag attribute 'item' is not specified\.\)\.
* \_\_total\_\_   : Count an array or objects for loop\.

### foreach

Alias for 'loop'

### for

The block is repeatedly output during the specified value\.

#### attributes

* to\(end or loop\) : The ending number for the loop\(default:1\)\. 
* from\(start\)     : The starting number for the loop\(default:0\)\.
* increment\(step\) : The amount to increment the loop counter\(default:1\)\.
* var               : Assigned to template variable to 'var'\(default:'\_\_value\_\_'\)\.
* glue              : When specified, the string will be placed in between each row\.

#### reserved variables

Within this tag, the following variables are assigned

* \_\_first\_\_   : Assigned when the loop is in the first iteration\.
* \_\_last\_\_    : Assigned when the loop is in the last iteration\.
* \_\_odd\_\_     : Assigned true when the loop is on odd numbered rows\.
* \_\_even\_\_    : Assigned true when the loop is on even numbered rows\.
* \_\_counter\_\_ : Loop counter\.
* \_\_index\_\_   : Loop counter\(Starting from 0\)\.
* \_\_value\_\_   : Holds the 'value' of the array or objects for loop\(When the tag attribute 'var' is not specified\.\)\.
* \_\_total\_\_   : Total loop count\.

### section

Alias for 'for'.

### ignore

Always produces an empty string\.

### literal

Allow a block of data to be taken literally\. Anything within this tags is not interpreted, but displayed as\-is\.

### nocache

Make sure that certain parts of the template are not page cached\.

### setvarblock

A block tag used to set the value of a template variable\. Note that you can also use the 'setvar' modifier to achieve the same result as it can be applied to any tag\.

#### attributes

* name\(required\) : A name for set variable\.
* append           : Append a value to the end of an existing variable\.
* prepend          : Prepend a value to the end of an existing variable\.

### capture

Alias for 'setvarblock'\. Use attribute 'var' instead 'name'\.

### setvars

Set variables collectively\. The key and value delimiter is '='\. When specified 'name' attribute, assigned to array to 'name'\.

    <paml:setvars>
    _url       =http://www.example.com/
    _site_name =PAML
    _site_desc =<paml:var name="description">
    </paml:setvars>

### assignvars

Alias for 'setvars'.

## Conditional Tags

### if

A conditional tag that is evaluated if the name attributes evaluate true\. This tag can be used in combination with the 'elseif' and 'else' tags\.

#### attributes

* name\(required\) : A Name for evaluate\.
* eq               : Given value is equal to the variable\.
* ne\(not\)        : Given value is not equal to the variable\.
* gt               : Given value is greater than the variable\.
* lt               : Given value is less than the variable\.
* ge               : Given value is greater than or equal to the variable\.
* le               : Given value is less than or equal to the variable\.
* like             : Given value contains the variable\.
* test             : By expression result\.

### ifgetvar

It simply branches on the presence or absence of the value of the variable specified in the name attribute, and it is faster than the if statement\.

### else

Used within If and Unless blocks to output the alternate case\.

### elseif

Used within If and Unless blocks to evaluated alternate condition\. All attributes supported by the 'if' tag are also supported\.

### elseifgetvar

Used within If block to evaluated alternate condition\. Like the ifgetvar condition tag, It simply branches on the presence or absence of the value of the variable specified in the name attribute\.
It's faster than the elseif statement\.

### unless

The logical opposite of the 'if' tag\. All attributes supported by the 'if' tag are also supported\.

## Include tags

### include

Includes the content of external file into the current template\. It is not included in property $include\_paths The directory trace distribution file can not be included\.

#### attributes

* file\(required\) : File path for include\.
* Other Parameters be passed to the included template file as custom attribute\.

### includeblock

Write this tag like a block tag\. Includes the content of external file into the current template\. It is not included in property $include\_paths The directory trace distribution file can not be included\. Block content to set variable 'contents'\.

#### attributes

* file\(required\) : File path for include\.
* Other Parameters be passed to the included template file as custom attribute\.

### extends

This tags are used in child templates in template inheritance for extending parent templates. For details see section of Template Interitance.  
The tag must be on the first line of the template.

#### attributes

* file\(required\) : The name of the template file which is extended\.

## Modifiers

### escape

Encodes any special characters to HTML entities or URL encode\.

#### attribute

* "1" or "html" \(to HTML entities \) or "url"

### setvar

Used to set a output value to variable. Output empty string.

#### attribute

* A name for set variable\.

### format_ts

Format the date\.

### remove_html

Strip HTML and PHP tags from a string\.

### encode_js

Encodes special characters in JavaScript\.

### upper_case

Make a string uppercase\.

### lower_case

Make a string lowercase\.

### trim

Strip whitespace (or other characters) from the beginning and end of a string.

### ltrim

Strip whitespace \(or other characters\) from the beginning of a string\.

### rtrim

Strip whitespace \(or other characters\) from the end of a string\.

### truncate

Truncates a variable to a character length \( or trims the variable \)\.

See =&gt; [http://www.smarty.net/docsv2/en/language.modifier.truncate](http://www.smarty.net/docsv2/en/language.modifier.truncate) 
or [https://movabletype.org/documentation/appendices/modifiers/trim-to.html](https://movabletype.org/documentation/appendices/modifiers/trim-to.html)

### trim_to

Alias for 'truncate'\.

### zero_pad

Adds '0' to the left of string to the length specified\.

### strip_linefeeds

Removes any linefeed characters\.

### sprintf

Return a formatted string\.

### nl2br

Inserts HTML line breaks before all newlines in a string\.

### replace

Replace all occurrences of the search string with the replacement string\.

### regex\_replace

A regular expression search and replace on a variable\. Use the [preg\_replace\(\)](http://php.net/manual/ja/function.preg-replace.php) syntax from the PHP manual\.

### wrap 

Wraps a string to a given number of characters\.

### trim_space

Specified 1, trim whitespace\. Specified 2, trim linefeed\. Specified 3, both\.

### to_json

JSON representation of a value to template variable\.
            
### from_json

Decodes a JSON string from template variable\.

### eval

Build template from string\.

## Template Inheritance

Inheritance brings the concept of Object Oriented Programming to templates\.  
Replace the block tag with the name attribute of the parent template specified by the extends tag at the top of the child template with the result of the block tag specified by the child template\.  
See =&gt; [http://www.smarty.net/docs/en/advanced.features.template.inheritance.tpl](http://www.smarty.net/docs/en/advanced.features.template.inheritance.tpl)

## Implement Template Tags

### Function Tag

Function tags are functions of the form

    <paml:var name="name">

#### parameters

* array   $args   : tag attributes
* object  $ctx    : Object class PAML

The output \(return value\) of the function will be substituted in place of the function tag in the template, e\.g\. the &lt;paml:var&gt; function\.  
Alternatively, the function can simply perform some other task without any output, e\.g\. the &lt;paml:setvar&gt; function\.

### Block Tag

Block tags are functions of the form

    <paml:block> ... </paml:block>

Block tag returns output $content\.

#### parameters

* array   $args     : tag attributes
* string  &$content : \*1 
* object  $ctx      : Object class PAML
* boolean &$repeat  : \*2
* numeric $counter  : loop counter value

\*1 $content is the template output\.  
In case of first loop, it will be null, and in after the second time loop it will be the contents of the template block\.

\*2 $repeat is true at the first call of the block function and false on all subsequent calls to the block function\.  
Each time the function implementation returns with $repeat being true, the contents between &lt;paml:block&gt; \.\.\. &lt;/paml:block&gt; are evaluated and the function implementation is called again with the new block contents in the parameter $content\.

Simplest implementation example

    function some_block_tag ( $args, $content, $ctx, &$repeat, $counter ) {
        return ( $counter ) ? $content : null;
    }
 
### BlockOnce Tag

BlockOnce\(block\_once\) tags are functions of the form

    <paml:block>...</paml:block>

BlockOnce tag execute only once\.  
The tag implementation is the same as the Block tag\.  
The parameter $counter always 1\(numeric\)\.

### Conditional Tag

Conditional tags are functions of the form

    <paml:if name="name"> ... </paml:if>

&lt;paml:else&gt; and &lt;paml:elseif&gt; are also permitted\.
Conditional tag returns true or false;

#### parameters

* array   $args    : Tag attributes
* string  $content : Always null
* object  $ctx     : Object class PAML
* boolean $repeat  : Always true
* boolean $context : if specified false, Behave as unless\.

Creating a new conditional tag, tag attributes such as eq, ne, like can be used by returning $ctx\-&gt;conditional\_if instead of returning true or false\.

### Modifier

Modifiers are little functions that are applied to a variable in the template before it is displayed\. Modifiers can be chained together\.

#### parameters

* mixed   $str     : Template output
* mixed   $arg     : Tag attribute
* object  $ctx     : Object class PAML
* string  $name    : Name of called modifier

or Smarty2\(BC\) style plugin

* string  $str     : Template output
* mixed   $arg1    : Tag attribute1
* mixed   $arg2    : Tag attribute2\.\.\.

if exists modifier encode\_javascript in plugin, escape=&quot;javascript&quot; is equivalent to encode\_javascript=&quot;1&quot;

## Implement Callbacks

Callbacks always returns the value of the first argument\( or after processing first argument \) unless there is any special intention\.

### input\_filter \( $content, $ctx \) or output\_filter \( $content, $ctx \)

#### parameters

* string  $content   : template source \(input\_filter\) or output content \(output\_filter\)
* object  $ctx       : Object class PAML

### pre\_parse\_filter\( $content, $ctx, $insert \)

Call at before DOMDocument::loadHTML\.

#### parameters

* string  $content   : template content for loadHTML\.
* object  $ctx       : Object class PAML
* string  $insert    : insert\_text\(Measures against the problem that blank characters disappear\.\)

### dom\_filter\( $dom, $ctx \)

Call at before DOMDocument::saveHTML\.

#### parameters

* object  $dom       : object DOMDocument
* object  $ctx       : Object class PAML

### post\_compile\_filter\( $content, $ctx \)

Call at after DOMDocument::saveHTML\.

#### parameters

* string  $content   : compiled template source
* object  $ctx       : Object class PAML

## Register plugins, tags and callbacks

There is a definition of class ClassName in plugins/PluginID/class\.ClassName\.php,  
Registration is done automatically\.

### $ctx->register_component( $plugin, $path, $registry );

#### parameters

* object  $plugin    : Plugin class
* string  $path      : Plugin directory path\( \_\_DIR\_\_\)
* array   $registry  : An array of template tags or callbacks\(or $registry to file 'config\.json'\)\.

### $ctx->register_tag( $tag_name, $tag_kind, $func_name, $plugin );

#### parameters

* string  $tag\_name  : Tag name
* string  $tag\_kind  : Tag kind \(function, block, block\_once, conditional include or modifier\)
* string  $func\_name : Plugin class's method name
* object  $plugin     : Plugin class

### $ctx\->register\_callback\( $id, $type, $func\_name, $plugin \);

#### parameters

* string  $id         : Callback id \(It needs to be unique by $type\)
* string  $type       : Callback type \(input\_filter, output\_filter, loop\_filter, conditional\_filter or dom\_filter\)
* string  $func\_name : Plugin class's method name
* object  $plugin     : Plugin class

## Methods

### $ctx\->assign\( $name, $value \);

Set variables\.

#### parameters

* mixed   $name      : Name of set variables or array variables\.
* array   $value     : Variable for set variables\.

### $ctx\->stash\( $name, $value \);

Where the variable is stored\.

#### parameters

* string  $name      : Name of set or get variable to\(from\) stash\.
* mixed   $value     : Variable for set to stash\.

#### return

* mixed  $var        : Stored data\.

### $ctx\->build\_page\( $path, $params, $cache\_id, $disp, $src \);

Build template file and display or return result\.

#### parameters

* string  $path      : Template file path\.
* array   $params    : Array of template variables to set\.
* string  $cache\_id  : Template cache id\.
* bool    $disp      : Display result or return result\.
* string  $src       : Template source text\.

#### return

* string  $content   : After processing $content\.

### $ctx\->fetch\( $path, $params, $cache\_id \);

Build template file and return result\(Do not display\)\.

#### parameters

* string  $path      : Template file path\.
* array   $params    : Array of template variables to set\.
* string  $cache\_id  : Template cache id\.

#### return

* string  $content   : After processing $content\.

### $ctx\->display\( $path, $params, $cache\_id \);

Build template file and display result and return result\.

#### parameters

* string  $path      : Template file path\.
* array   $params    : Array of template variables to set\.
* string  $cache\_id  : Template cache id\.

#### return

* string  $content   : After processing $content\.

### $ctx\->render\( $src, $params, $cache\_id \);

Build template from content\.

#### parameters

* string  $src       : Template source text\.
* array   $params    : Array of template variables to set\.
* string  $cache\_id  : Template cache id\.

#### return

* string  $content   : After processing $content\.

### $ctx\->build\( $src, $compiled = false \);

Build from source text always it does not use caching\.

#### parameters

* string  $src       : Template source text\.
* bool    $compiled  : Get compiled PHP code\.

#### return

* string  $build     : After processing $src\.

### $ctx\->set\_loop\_vars\( $counter, $params \);

Set common loop variables \( '\_\_index\_\_', '\_\_counter\_\_', '\_\_odd\_\_',
'\_\_even\_\_', '\_\_first\_\_', '\_\_last\_\_', '\_\_total\_\_' \) by $counter and array or 
objects for loop\.

#### parameters

* int     $counter   : Loop counter\.
* array   $params    : Array or object for loop\.

### $ctx\->localize\( \[ 'name1', 'name2', \[ 'name3', 'name4' \] \] \);
### $ctx\->restore \( \[ 'name1', 'name2', \[ 'name3', 'name4' \] \] \);

Localize the scope of the variable\.  
In the first iteration \( $counter == 0 \), call localize and in the last iteration, call restore\.  
When passing an array in array as an argument,  localize or restore $ctx\->\_\_stash\[ 'vars' \]\[ $value \]
of the value of each array\.
When passing an string in array as an argument, localize or restore $ctx\->stash\( $value \)\.

### $ctx\->get\_any\( $key \);

Get variable matching one of $local\_vars\[ $name \] or $vars\[ $name \]\.

### $ctx\->setup\_args\( $args \);

Setup tag attributes\. value, variable, or array\(CSV\)\.  
When $advanced\_mode is true, call automatically\.

### $ctx\->configure\_from\_json\( $json \);

Set properties from JSON file\.

#### parameters

* string  $json      : JSON file path\.

## Properties\(Initial value\)

### $vars\(\[\]\)

Global template variables\.

### $\_\_stash\(\[\]\)

$\_\_stash\['vars'\] is alias for $vars\.

### $local\_vars\(\[\]\)

Localized variables in block scope\.

### $local\_params\(\[\]\)

Localized array or object for loop\.

Property $local\_vars and $local\_params are always the localized variables of the block scope\.  
When exiting the block it is reassigned to its original variables\.

### $prefix\('paml'\)

Tag prefix\.

### $tag\_block\(\['{%', '%}'\]\)

Tag delimiters\.

### $ldelim, $rdelim

Alias for $tag\_block\.

### $cache\_ttl\(3600\)

Page cache expiration seconds\.

### $force\_compile\(true\)

This forces PAML to \(re\)compile templates on every invocation\.

### $caching\(false\)

Whether or not to cache the output of the templates\.

### $compile\_check\(true\)

Check to see if the current template has changed \(different time stamp\) since the last time it was compiled\.

### $cache\_driver\(null\)

Cache driver\('Memcached' or null\)\. If null, use simple file cache\.  
Use 'Memcached', lib/cachedriver\.base\.php and lib/cachedriver\.memcached\.php are required\.

    $ctx->cache_driver = 'Memcached';
    $ctx->memcached_servers = [ 'localhost:11211' ];

### $advanced\_mode\(true\)

If false, The tag arguments specification is value only\(It is not possible to specify array with key, variable, or array\(CSV\)\) but depending on the template, it may be several to 10% faster\.

### $csv\_delimiter\(':'\)

CSV delimiter for parse tag attributes\.

### $csv\_enclosure\("'"\)

CSV enclosure for parse tag attributes\.

### $autoescape\(false\)

Everything is escaped\(When there is no designation of 'raw' modifier\)\.

### $debug\(false\)

$debug = 1: error\_reporting\( E\_ALL \) / 2: debugPrint error\. / 3. debugPrint compiled code\.

### $includes\(\['txt', 'tpl', 'tmpl', 'inc', 'html'\]\)

An array of file extensions that allow include\.

## Smarty2(BC) style plugin support

When $ctx->plugin\_compat = 'smarty\_',

### Function Tag

function smarty\_function\_&lt;prefix&gt;functionname in plugins/function\.&lt;prefix&gt;functionname\.php

### Block Tag

function smarty\_block\_<prefix>blockname in  
plugins/block\.<prefix>blockname\.php

### Conditional Tag

function smarty\_block\_&lt;prefix&gt;ifconditionalname in  
plugins/block\.&lt;prefix&gt;ifconditionalname\.php

### Modifier

function smarty\_modifier\_modifiername in  
plugins/modifier\.modifiername\.php

## Localization support

Using class PAML's language property\.  
When not specified, $\_SERVER\[ 'HTTP\_ACCEPT\_LANGUAGE' \] automatically in use\.

plugins/PluginID/locale/&lt;language&gt;\.json

### Example\(ja\.json\)
    {
    "Welcome to %s!":"%sへようこそ!"
    }

### in Template

    <paml:trans phrase="Welcome to %s!" params="PAML" component="PAML">
