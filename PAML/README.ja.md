# PAML : PHP Alternative Markup Language

* バージョン    1\.0
* 作成者     Alfasado Inc\. &lt;webmaster@alfasado\.jp&gt;
* ©  2017 Alfasado Inc\. All Rights Reserved\.

## 動作環境

* 動作環境: PHP5\.6 もしくは PHP7以上
* 文字コードはUTF\-8のみをサポート

## 概要

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

## マークアップ記法

テンプレート・タグは大文字小文字を考慮せずに記述できます。タグの先頭と最後に'$' を書いてもよく、
接頭子の後に':'を付与することができます。終了タグの"/"は省略可能です。  
引数の不要なモディファイアでは、属性値を省略できます。

### ファンクションタグ\(タグ属性とモディファイア\)

    <pamlvar name="name" escape>, <pamlvar name="name" escape="1">,
    <PAMLVar name="name" escape />, <paml:getVar name="name" escape="1" />,
    <Paml:var name="name" escape="1" />, <$paml:Getvar name="name" escape="1"$>,
    <paml:Var name="name" escape="1" />...

### インライン変数

    <paml:setvar name="name" value="value">
    <paml:name> は <paml:var name="name"> と等価です。
        あるいは
    <paml:array.key1.key2>

以下のテンプレートは意図通りに動作しません。インライン変数よりもテンプレート・タグが優先されるからです。

            <paml:setvar name="var" value="value">
            <paml:$var> <= これは、OK
            <paml:var>  <= これは、'var'ファンクションタグと解釈されます。

            <paml:setvar name="variable" value="value">
            <paml:$variable> <= これは、OK
            <paml:variable>  <= これも、'var'ファンクションタグと解釈されます。
    
このような問題を解消するためには、変数の頭に何らかの接頭子\('\_'など\)を付与するか、明示的に変数名の先頭に$を付与するとよいでしょう。

### ブロックタグ\(タグ属性とモディファイア\)

    <paml:block param1="1" param2="2"> ... </paml:block>

### 条件タグ\(タグ属性とモディファイア\)

    <paml:if name="variable_name1"> 
        ...
    <paml:elseif name="variable_name2">
        ...
    <paml:else>
        ...
    </paml:if>

### 条件タグ\(タグ属性とモディファイア\)

文字列、変数($から始まるか.を含む)、または配列(CSV)で値を渡します。

            value    : <paml:var name="name">
            array    : <paml:var name="array[key]">
                  or : <paml:var name="array.key1.key2">
            variable : <paml:var name="$variable">
                  or : <paml:var name="$variable[key]">
            request variable :
                       <paml:var name="request.name">
                                    (HTTPリクエスト変数'$_REQUEST'から値を取得します。)
            csv      : <paml:var name="name" replace="'value1':'value\'2'">
                  or : <paml:var name="name" replace="value1:value2">
                                    (<=タグの実装により)

            CSVのフィールドの囲み文字、フィールドの区切り文字はクラスのプロパティです(変更可能です)。
                規定値 : $csv_enclosure = "'" (囲み文字)
                        $csv_delimiter = ':' (区切り文字)

## テンプレート・タグ

### ファンクションタグ

### var

変数から値を呼び出します。
タグ属性 value を指定した場合は setvar ファンクションタグと同じ動作になります。

#### タグ属性

* name\(必須\): 変数名  
* value       : 値をセットする場合、その値
* append      : 既存の変数の後ろに、指定した値を連結します
* prepend     : 既存の変数の前に、指定した値を連結します

### getvar

テンプレート変数の値を出力します。変数名が文字列であることがわかっている場合、'var'ではなく、こちらを指定してください\(より高速\)。

### setvar

テンプレート変数に値を設定します。

#### タグ属性

* name\(必須\): 変数名
* var         : 'name' の別名
* value       : セットする値
* append      : 既存の変数の後ろに、指定した値を連結します
* prepend     : 既存の変数の前に、指定した値を連結します

### assign

'setvar'の別名です。

### trans

値を翻訳します。 参考 =&gt; ./locale/ja.json

#### タグ属性

* phrase : 翻訳する文字列
* params : sprintf 関数を使ってフォーマット整形する場合、渡す値  
　　　　　複数の値を指定するときは CSVを指定します
* component : プラグイン・クラスの名前

### ldelim

タグの開始文字列を出力します\(規定値 '\}\}'\)\.

### rdelim

タグの終了文字列を出力します\(規定値 '}}'\)\.

## Block Tags

### block

囲まれたブロックを1回だけ処理して内容を出力します。  
name 属性が指定されていて、変数に値が格納されている場合はそちらが出力されます。

#### タグ属性

* name    : 結果を出力せずに変数に結果を格納します
* append  : コンテンツを親テンプレートの block に追記します
* prepend : コンテンツを親テンプレートの block の前に置きます。
* その他の値を指定すると、ブロック内部でのみ利用できるローカル変数になります。

### loop

タグ属性 name または from で指定された配列またはオブジェクトをループ出力します。

#### タグ属性

* name\(必須\): ループ出力する配列またはハッシュの変数名
* from       : 'name' の別名
* key        : 配列またはハッシュの「キー」を格納する変数名
* item       : 配列またはハッシュの「値」を格納する変数名
* sort\_by   : 配列の並べ替えの定義\(例: sort\_by="value,numeric,reverse"\)
* glue       : 繰り返し処理の際に、指定された文字列で各ブロックを連結します

#### 予約変数

ループの回数に応じて自動的に以下の変数がセットされます

* \_\_first\_\_   : ループの初回
* \_\_last\_\_    : ループの最終回
* \_\_odd\_\_     : ループの奇数回
* \_\_even\_\_    : ループの偶数回
* \_\_counter\_\_ : ループのカウンター
* \_\_index\_\_   : ループのカウンター\(0から始まる\)
* \_\_key\_\_     : 配列またはハッシュのキー\(タグ属性'key'の指定のない場合\)
* \_\_value\_\_   : 配列またはハッシュの値\(タグ属性'item'の指定のない場合\)
* \_\_total\_\_   : 配列またはオブジェクトの数

### foreach

'loop'の別名です。

### for

指定された値の間、ブロックを繰り返し出力します。  

#### タグ属性

* to\(end or loop\) : 値を越えた場合、ループは終了します。省略した場合、1回ループします 
* from\(start\)     : ループの初期値。省略時は１となります
* increment\(step\) : 1回のループで増加する値(省略時は、1ずつ増加します)
* var               : 「値」を格納する変数名\(省略時は予約変数'\_\_value\_\_'\)
* glue              : 繰り返し処理の際に、指定された文字列で各ブロックを連結します

#### 予約変数

ループの回数に応じて自動的に以下の変数がセットされます。

* \_\_first\_\_   : ループの初回
* \_\_last\_\_    : ループの最終回
* \_\_odd\_\_     : ループの奇数回
* \_\_even\_\_    : ループの偶数回
* \_\_counter\_\_ : ループのカウンター
* \_\_index\_\_   : ループのカウンター\(0から始まる\)
* \_\_value\_\_   : 配列またはハッシュの値\(タグ属性'var'の指定のない場合\)
* \_\_total\_\_   : 配列またはオブジェクトの数

### section

'for'の別名です。

### ignore

このブロックの中は出力されません\(テンプレート・コメント\)。

### literal

ブロックの内容はビルドされず、 そのまま表示されます。

### nocache

テンプレートの特定の部分をページキャッシュの対象外にします。

### setvarblock

ブロックの内容を出力する代わりに変数に格納します。
これは、何らかのブロックタグに'setvar'モディファイアを指定した時の動作と同じです。

#### タグ属性

* name\(必須\) : 変数名
* append      : 既存の変数の後ろに、指定した値を連結します
* prepend     : 既存の変数の前に、指定した値を連結します

### capture

setvarblock'の別名です。'name'属性の代わりに'var'属性を使ってください。

### setvars

各行ごとに記述された変数をまとめて設定します。キーと値の区切り文字は '='です。  
'name'属性を指定すると、その変数名に配列をセットします。

    <paml:setvars>
    _url       =http://www.example.com/
    _site_name =PAML
    _site_desc =<paml:var name="description">
    </paml:setvars>

### assignvars

'setvars'の別名です。

## 条件タグ

### if

条件を満たした場合に内容を出力します。条件を満たさない場合に実行する場合は、unless 条件タグを使用するか、if 条件タグの中で else, elseif 条件タグを利用します。

#### タグ属性

* name\(必須\) : 変数名
* eq          : 変数の値が属性値と同等である
* ne\(not\)   : 変数の値が属性値と同等でない
* gt          : 変数の値が属性値より大きい
* lt          : 変数の値が属性値より小さい
* ge          : 変数の値が属性値以上
* le          : 変数の値が属性値以下
* like        : 変数の値が属性値を含む
* test        : 式を評価する

### ifgetvar

単に、name属性に指定した変数の値の有無で分岐し、if文より高速です。

### else

if または unless ブロックの中で、条件に一致しなかったときにこのタグ以降の内容が出力されます。

### elseif

if または unless ブロックの中で、別の条件を指定する時にこの条件タグを利用します。if 条件タグと同じタグ属性が利用できます。

### elseifgetvar

if ブロックの中で、別の条件を指定する時にこの条件タグを利用します。  
ifgetvar 条件タグと同じくname属性に指定した変数の値の有無で分岐し、elseif文より高速です。

### unless

条件を満たさなかった場合に内容を出力します。if 条件タグと同じタグ属性が利用できます。

## インクルードタグ

### include

現在のテンプレートに外部ファイルの内容を含めます。$include_paths プロパティに含まれていないディレクトリ配下のファイルを含めることはできません。

#### タグ属性

* file\(必須\) : インクルードするファイルのパス
* その他のタグ属性で指定した値はインクルードしたファイル内部でのみ利用できるローカル変数になります。

### includeblock

このタグはブロックタグのように記述します。  
include タグと同じくテンプレートモジュールを読み込みます。$include\_paths プロパティに含まれていないディレクトリ配下のファイルを含めることはできません。このタグで囲まれたコンテンツは、このファイルの中だけで利用できる変数'contents'にセットされます。

### extends

テンプレートの継承で親テンプレートを継承するときに使います。  
このタグは、テンプレートの最初の行に書かなければなりません。

* file\(必須\) : 継承するテンプレート・ファイルのパス

## モディファイア

### escape

値をHTMLエンティティに変換または urlエンコードします。

#### 属性値

* "1" or "html" \(HTMLエンティティに変換\) or "url"

### setvar

出力されるべき値を出力せずに変数に格納します。

#### attribute

* 変数名

### format_ts

日付文字列をフォーマットします。

### remove_html

HTMLタグとPHPタグを削除します。

### encode_js

JavaScriptの文字列として扱えるように値をエスケープします。

### upper_case

文字列を大文字にします。

### lower_case

文字列を小文字にします。

### trim

文字列の先頭および末尾にあるホワイトスペースを取り除きます。

### ltrim

文字列の最初から空白(もしくはその他の文字)を取り除きます。

### rtrim

文字列の最後から空白(もしくはその他の文字)を取り除きます。

### truncate

指定したキャラクタ数で値を切り捨てます。

参考 =&gt; [http://www.smarty.net/docsv2/ja/language.modifier.truncate.tpl](http://www.smarty.net/docsv2/ja/language.modifier.truncate.tpl) 
or [https://www.movabletype.jp/documentation/appendices/modifiers/trim_to.html](https://www.movabletype.jp/documentation/appendices/modifiers/trim_to.html)

### trim_to

'truncate'の別名です。

### zero_pad

指定した文字数になるよう、先頭の余白を0で埋めます。

### strip_linefeeds

改行を削除します。

### sprintf

フォーマットされた文字列を返します。

### nl2br

改行文字の前に HTML の改行タグを挿入します。

### replace

検索文字列に一致したすべての文字列を置換します。

### regex\_replace

変数に対して正規表現による検索・置換を行います。  
正規表現は、PHPマニュアルの [preg\_replace\(\)](http://php.net/manual/ja/function.preg-replace.php) の構文を使用してください。

### wrap 

指定した文字数でテキストを改行文字で折り返します。

### trim_space

1を指定すると、ホワイトスペースをトリミングします。
2を指定すると、改行文字をトリミングします。
3を指定した場合、その両方となります。

### to_json

テンプレート変数を指定してJSON文字列を出力します。
            
### from_json

JSON文字列デコードして指定した変数にセットします。

### eval

テンプレートをビルドします。

## テンプレートの継承

継承機能は、オブジェクト指向プログラミングの考え方をテンプレートに導入したものです。  
子テンプレートの先頭で extendsタグによって指定された親テンプレートの name属性付きblockタグを子テンプレートで指定した blockタグの結果に置き換えます。  
参考 =&gt; [http://www.smarty.net/docs/ja/advanced.features.template.inheritance.tpl](http://www.smarty.net/docs/ja/advanced.features.template.inheritance.tpl)

## テンプレート・タグの実装

### ファンクションタグ

ファンクションタグは、次のように記述されるタイプのタグです。

    <paml:var name="name">

#### パラメタ

* array   $args   : タグ属性の配列
* object  $ctx    : クラス PAML

メソッドの戻り値はテンプレート関数のタグの部分と置き換えられます\(例: &lt;paml:var&gt;\)。  
あるいは何も出力せずに単に他のタスクを実行する事ができます\(例: &lt;paml:setvar&gt;\)。

### Block Tag

Block tags are functions of the form

    <paml:block> ... </paml:block>

ブロックタグは、次のように記述されるタイプのタグです。ブロックタグでは、出力される $content を戻り値に指定します。

#### パラメタ

* array   $args     : タグ属性の配列
* string  &$content : \*1 
* object  $ctx      : クラス PAML
* boolean &$repeat  : \*2
* numeric $counter  : ループのカウンタ

\*1 $content にはテンプレートの出力結果がセットされます。  
$content は最初のループでは null、2回目以降のループではテンプレートブロックのコンテンツがセットされています。  

\*2 $repeat は最初のループでは true、2回目以降のループでは falseがセットされています。  
タグの中で $repeat を true にセットすると、 &lt;paml:block&gt; \.\.\. &lt;/paml:block&gt;
ブロック内は繰り返しビルドされ、$content パラメータに新しいブロックコンテンツが格納された状態で再び呼び出されます。

最もシンプルなブロックタグの実装例\(1度だけブロックがビルドされます\)。

    function some_block_tag ( $args, $content, $ctx, &$repeat, $counter ) {
        return ( $counter ) ? $content : null;
    }
 
### ブロックタグ\(block\_once\)

ブロック\(block\_once\)タグには、次のように記述されるタイプのタグです。

    <paml:block>...</paml:block>

ブロック\(block\_once\)タグは一度だけコールされます。
ブロック\(block\_once\)タグの実装はブロックタグと同様です。パラメタ $counter には1が渡されます。

### 条件タグ

条件タグは、次のように記述されるタイプのタグです。

    <paml:if name="name"> ... </paml:if>

&lt;paml:else&gt; と &lt;paml:elseif&gt; が利用可能です。
条件タグは true か false のいずれかを返します。

#### パラメタ

* array   $args    : タグ属性の配列
* string  $content : 常に null
* object  $ctx     : クラス PAML
* boolean $repeat  : 常に true
* boolean $context : false を指定すると unless として動作します。

新たに条件タグを作成する際は、true、false を返すかわりに $ctx\-&gt;conditional\_if を返すことによって eq、ne、likeなどのタグ属性を利用できるようになります。

### モディファイア

モディファイアは、テンプレートの変数が表示される前または他のコンテンツに使用される前に適用される関数です。  
モディファイアは複数指定できますが、同じモディファイアを一つのテンプレート・タグに指定することはできません。

#### パラメタ

* string $str  : テンプレートの出力
* mixed  $arg  : 属性値
* object $ctx  : クラス PAML
* string $name : 呼び出されたモディファイアの名前

もしくは Smarty2(BC) スタイルのプラグインでは

* string $str  : テンプレートの出力
* mixed  $arg1 : 属性値1
* mixed  $arg2 : 属性値2\.\.\.

プラグイン内でモディファイア encode\_javascript を定義した場合、escape=&quot;javascript&quot; 指定で encode\_javascript=&quot;1&quot; と同等の結果が返ります。

## コールバックの実装

コールバックでは、\(出力を制御したいなどの特別な理由のない限り\)第一引数の値もしくは第一引数の値を加工したものを返す必要があります。

### input\_filter \( $content, $ctx \) または output\_filter \( $content, $ctx \)

#### パラメタ

* string $content : 入力ソース \(input\_filter\)もしくは 出力結果 \(output\_filter\)
* object $ctx     : クラス PAML

### pre\_parse\_filter\( $content, $ctx, $insert \)

DOMDocument::loadHTML がコールされる直前に呼び出されます。

#### パラメタ

* string $content : loadHTMLに渡されるコンテンツ
* object $ctx     : クラス PAML
* string $insert  : ダミー文字列\(DOMDocument::saveHTML で要素間の空白文字が消える問題への対策のため\)

### dom\_filter\( $dom, $ctx \)

DOMDocument::saveHTML がコールされる直前に呼び出されます。

#### パラメタ

* object $dom : クラス DOMDocument
* object $ctx : クラス PAML

### post\_compile\_filter\( $content, $ctx \)

DOMDocument::saveHTML がコールされた直後に呼び出されます。

#### パラメタ

* string $content : コンパイル済みのテンプレート
* object $ctx     : クラス PAML

## プラグイン、コールバック、テンプレートタグの登録

plugins/PluginID/class\.ClassName\.php にクラス「ClassName」の定義がある時、登録は自動的に行われます。

### $ctx->register_component( $plugin, $path, $registry );

#### パラメタ

* object $plugin  : プラグインクラス
* string $path    : プラグインディレクトリのパス\( \_\_DIR\_\_\)
* array $registry : タグとコールバックの配列\(もしくは $registry をJSON形式のデータにして'config\.json'ファイルに保存できます\)

### $ctx->register_tag( $tag_name, $tag_kind, $func_name, $plugin );

#### パラメタ

* string $tag\_name  : タグ名
* string $tag\_kind  : タグの種類 \(function, block, block\_once, conditional include または modifier\)
* string $func\_name : クラスのメソッド名
* object $plugin     : プラグインクラス

### $ctx\->register\_callback\( $id, $type, $func\_name, $plugin \);

#### パラメタ

* string  $id         : コールバックID \(コールバックタイプごとにユニークであること\)
* string  $type       : コールバックタイプ \(input\_filter, output\_filter, loop\_filter, conditional\_filter or dom\_filter\)
* string  $func\_name : クラスのメソッド名
* object  $plugin     : プラグインクラス

## メソッド

### $ctx\->assign\( $name, $value \);

変数に値をセットします。

#### パラメタ

* mixed   $name      : セットする変数名もしくは変数の配列
* array   $value     : セットする値の配列

### $ctx\->stash\( $name, $value \);

変数の格納場所です。

#### パラメタ

* string  $name      : セットまたは取得する変数名
* mixed   $value     : セットする値

#### 戻り値

* mixed  $var        : 格納された値

### $ctx\->build\_page\( $path, $params, $cache\_id, $disp, $src \);

テンプレートをビルドし、出力するか結果を返します。

#### パラメタ

* string  $path      : テンプレートファイルのパス
* array   $params    : テンプレート変数にセットする値の配列
* string  $cache\_id : キャッシュID
* bool    $disp      : 結果を出力するかどうか
* string  $src       : ファイルの代わりに利用するテンプレートのソース文字列

#### 戻り値

* string  $content   : ビルドされた結果のテキスト

### $ctx\->fetch\( $path, $params, $cache\_id \);

テンプレートをビルドし、出力せずに値を返します。

#### パラメタ

* string  $path      : テンプレートファイルのパス
* array   $params    : テンプレート変数にセットする値の配列
* string  $cache\_id : キャッシュID

#### 戻り値

* string  $content   : ビルドされた結果のテキスト

### $ctx\->display\( $path, $params, $cache\_id \);

テンプレートをビルドし、出力して値を返します。

#### パラメタ

* string  $path      : テンプレートファイルのパス
* array   $params    : テンプレート変数にセットする値の配列
* string  $cache\_id : キャッシュID

#### 戻り値

* string  $content   : ビルドされた結果のテキスト

### $ctx\->render\( $src, $params, $cache\_id \);

ファイルからではなく、テンプレートのソースからビルドします。

#### パラメタ

* string  $src       : テンプレートのソース
* array   $params    : テンプレート変数にセットする値の配列
* string  $cache\_id : キャッシュID

#### 戻り値

* string  $content   : ビルドされた結果のテキスト

### $ctx\->build\( $src, $compiled = false \);

テンプレートのソースからビルドした値を返します。コンパイル結果をキャッシュしません。

#### パラメタ

* string  $src       : テンプレートのソース
* bool    $compiled  : 指定した場合、ビルド結果ではなくコンパイル後のPHPコードを返す

#### 戻り値

* string  $build     : ビルドされた結果のテキスト

### $ctx\->set\_loop\_vars\( $counter, $params \);

カウンタ値とループ対象の配列変数またはオブジェクトから予約変数に値をまとめてセットします \( '\_\_index\_\_', '\_\_counter\_\_', '\_\_odd\_\_','\_\_even\_\_', '\_\_first\_\_', '\_\_last\_\_', '\_\_total\_\_' \)。
#### パラメタ

* int     $counter   : ループのカウンタ値
* array   $params    : ループ対象の配列変数またはオブジェクト

### $ctx\->localize\( \[ 'name1', 'name2', \[ 'name3', 'name4' \] \] \);
### $ctx\->restore \( \[ 'name1', 'name2', \[ 'name3', 'name4' \] \] \);

変数のスコープをローカライズします。  
ブロックの初回 \( $counter == 0 \)で localize をコールし、ブロックの最後で restore をコールしてください。
引数には、対象の変数名を配列で指定します。
配列の中に配列で指定されたものは $ctx\-&gt;\_\_stash\[ 'vars' \]\[ $value \] が対象となり、文字列を指定した場合は $ctx\-&gt;stash\( $value \) が対象となります。

### $ctx\->get\_any\( $key \);

$local\_vars\[ $key \] と $vars\[ $key \] のいずれかに値が存在する時に、その値を受け取ります。

### $ctx\->setup\_args\( $args \);

タグ属性値を\(文字列、変数\($から始まるか\.を含む\)、または配列\(CSV\)に\)セットします。  
$advanced\_mode が trueの時は、各タグの実行時に自動的に呼ばれます。

### $ctx\->configure\_from\_json\( $json \);

プロパティをJSONファイルに記述してまとめてセットします。

#### パラメタ

* string  $json      : JSON ファイルのパス

## プロパティ\(初期値\)

### $vars\(\[\]\)

グローバル・テンプレート変数。

### $\_\_stash\(\[\]\)

$\_\_stash\['vars'\] は $varsの別名。

### $local\_vars\(\[\]\)

ブロックスコープ内のローカル変数。.

### $local\_params\(\[\]\)

ブロックスコープで主にループ処理に使われる変数やオブジェクト。

プロパティ $local\_vars と $local\_params は常にローカル変数となります。  
ブロックを抜ける時には、これらは自動的にブロックの直前の状態に戻されます。

### $prefix\('paml'\)

タグ接頭子。

### $tag\_block\(\['{%', '%}'\]\)

タグ開始文字列と終了文字列。

### $ldelim, $rdelim

$tag_block のエイリアス。

### $cache\_ttl\(3600\)

ページキャッシュの有効期限\(秒\)。

### $force\_compile\(true\)

リクエスト毎にテンプレートをコンパイルするかどうか。

### $caching\(false\)

ページキャッシュを生成・利用するかどうか。

### $compile\_check\(true\)

現在のテンプレートが最後に訪れた時から変更されている(タイムスタンプが異なる)かどうかをチェックします。

### $cache\_driver\(null\)

キャッシュドライバ\('Memcached'もしくはnull\)。  
null指定の場合はシンプルなファイルキャッシュが使われます。'Memcached'の利用については lib/cachedriver\.base\.php と lib/cachedriver\.memcached\.php が必要です。

    $ctx->cache_driver = 'Memcached';
    $ctx->memcached_servers = [ 'localhost:11211' ];

### $advanced\_mode\(true\)

falseを指定すると、タグ属性に「値」のみが指定できるようになります\(変数や配列、CSVなどが利用できなくなります\)が、テンプレートによっては数%から10%程度高速になるかもしれません。

### $csv\_delimiter\(':'\)

CSVフィールド区切り文字。

### $csv\_enclosure\("'"\)

CSVフィールド囲み文字。

### $autoescape\(false\)

trueを指定すると、ファンクションタグの出力を自動エスケープします\(rawモディファイアの指定のないものすべて\)。

### $debug\(false\)

1を指定すると、error\_reporting( E\_ALL )に設定し、2を設定するとエラーを画面出力します。3を指定するとコンパイル済みテンプレートを出力します。

### $includes\(\['txt', 'tpl', 'tmpl', 'inc', 'html'\]\)

インクルード・タグによってインクルードを許可するファイル拡張子の配列です。

## Smarty2(BC) タイプのプラグインのサポート

$ctx\->plugin\_compat = 'smarty\_'; 指定の時、

### ファンクションタグ

plugins/function\.&lt;prefix&gt;functionname\.php の中の関数  
smarty\_function\_&lt;prefix&gt;functionname が実行されます。

### ブロックタグ

plugins/block\.&lt;prefix&gt;blockname\.php の中の関数  
smarty\_block\_&lt;prefix&gt;blockname が実行されます。

### 条件タグ

plugins/block\.&lt;prefix&gt;ifconditionalname\.php の中の関数  
smarty\_block\_&lt;prefix&gt;ifconditionalname が実行されます。

### Modifier

plugins/modifier\.modifiername\.php の中の関数  
smarty\_modifier\_modifiername が実行されます。

## 多言語サポート

クラス PAML の language プロパティが適用されます。  
指定のない場合 $\_SERVER\[ 'HTTP\_ACCEPT\_LANGUAGE' \] から自動的に設定されます。

plugins/PluginID/locale/&lt;language&gt;\.json

### サンプル\(ja\.json\)
    {
    "Welcome to %s!":"%sへようこそ!"
    }

### テンプレート

    <paml:trans phrase="Welcome to %s!" params="PAML" component="PAML">
