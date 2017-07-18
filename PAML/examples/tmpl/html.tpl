<TMPL_INCLUDE FILE="includes/header.tpl">

<h1><TMPL_TRANS PHRASE="Welcome to %s!" PARAMS="PAML"></h1>

<pre>
prefix : <TMPL_PROPERTY NAME="prefix">
ldelim : <TMPL_LDELIM ESCAPE=1>
rdelim : <TMPL_RDELIM ESCAPE=1>
</pre>

<TMPL_IF TEST="($foo==='foo'&&$bar==='bar')">
<p>TEST OK!</p>
</TMPL_IF>

<p><TMPL_DATE FORMAT="Y-m-d H:m:s T"></p>

<p><TMPL_VAR NAME="foo">, <TMPL_VAR NAME="bar">, <TMPL_VAR NAME="baz"></p>

<TMPL_LOOP NAME="loop_vars1">
<TMPL_IF NAME="__first__">
  <ul>
</TMPL_IF>
    <li class="<TMPL_IF NAME="__odd__">odd<TMPL_ELSE>even</TMPL_IF>">
        <TMPL_VAR NAME="__value__" ESCAPE=1="1">
    </li>
<TMPL_IF NAME="__last__">
  </ul>
</TMPL_IF>
</TMPL_LOOP>

<TMPL_LOOP NAME="loop_vars2" SORT_BY="value:reverse">
<TMPL_IF NAME="__first__">
  <ul>
</TMPL_IF>
    <li class="<TMPL_IF NAME="__odd__">odd<TMPL_ELSE>even</TMPL_IF>">
        <TMPL_VAR NAME="__key__"> : <TMPL_VAR NAME="__value__" ESCAPE=1="1">
    </li>
<TMPL_IF NAME="__last__">
  </ul>
</TMPL_IF>
</TMPL_LOOP>

<TMPL_SETVARS NAME="loop_vars3">
    4    =qux
    3    =baz
    2    =bar
    1    =foo
</TMPL_SETVARS>

<TMPL_LOOP NAME="loop_vars3" SORT_BY="key">
<TMPL_IF NAME="__first__">
  <ul>
</TMPL_IF>
    <li class="<TMPL_IF NAME="__odd__">odd<TMPL_ELSE>even</TMPL_IF>">
        <TMPL_VAR NAME="__key__"> : <TMPL_VAR NAME="__value__" ESCAPE=1="1">
    </li>
<TMPL_IF NAME="__last__">
  </ul>
</TMPL_IF>
</TMPL_LOOP>
<hr>
<pre>
<TMPL_LITERAL ESCAPE=1="1">
<TMPL_INCLUDE FILE="includes/header.tpl">

<h1><TMPL_TRANS PHRASE="Welcome to %s!" PARAMS="PAML"></h1>

<pre>
prefix : <TMPL_PROPERTY NAME="prefix">
ldelim : <TMPL_LDELIM ESCAPE=1>
rdelim : <TMPL_RDELIM ESCAPE=1>
</pre>

<TMPL_IF TEST="($foo==='foo'&&$bar==='bar')">
<p>TEST OK!</p>
</TMPL_IF>

<p><TMPL_DATE FORMAT="Y-m-d H:m:s T"></p>

<p><TMPL_VAR NAME="foo">, <TMPL_VAR NAME="bar">, <TMPL_VAR NAME="baz"></p>

<TMPL_LOOP NAME="loop_vars1">
<TMPL_IF NAME="__first__">
  <ul>
</TMPL_IF>
    <li class="<TMPL_IF NAME="__odd__">odd<TMPL_ELSE>even</TMPL_IF>">
        <TMPL_VAR NAME="__value__" ESCAPE=1="1">
    </li>
<TMPL_IF NAME="__last__">
  </ul>
</TMPL_IF>
</TMPL_LOOP>

<TMPL_LOOP NAME="loop_vars2" SORT_BY="value:reverse">
<TMPL_IF NAME="__first__">
  <ul>
</TMPL_IF>
    <li class="<TMPL_IF NAME="__odd__">odd<TMPL_ELSE>even</TMPL_IF>">
        <TMPL_VAR NAME="__key__"> : <TMPL_VAR NAME="__value__" ESCAPE=1="1">
    </li>
<TMPL_IF NAME="__last__">
  </ul>
</TMPL_IF>
</TMPL_LOOP>

<TMPL_SETVARS NAME="loop_vars3">
    4    =qux
    3    =baz
    2    =bar
    1    =foo
</TMPL_SETVARS>

<TMPL_LOOP NAME="loop_vars3" SORT_BY="key">
<TMPL_IF NAME="__first__">
  <ul>
</TMPL_IF>
    <li class="<TMPL_IF NAME="__odd__">odd<TMPL_ELSE>even</TMPL_IF>">
        <TMPL_VAR NAME="__key__"> : <TMPL_VAR NAME="__value__" ESCAPE=1="1">
    </li>
<TMPL_IF NAME="__last__">
  </ul>
</TMPL_IF>
</TMPL_LOOP>
<TMPL_INCLUDE FILE="includes/footer.tpl">
</TMPL_LITERAL>
</pre>

<TMPL_INCLUDE FILE="includes/footer.tpl">