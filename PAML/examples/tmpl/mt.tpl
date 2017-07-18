<mt:include file="includes/header.tpl">

<h1><mt:trans phrase="Welcome to %s!" params="PAML"></h1>

<pre>
prefix : <mt:property name="prefix">
ldelim : <mt:ldelim escape>
rdelim : <mt:rdelim escape>
</pre>

<mt:if test="($foo==='foo'&&$bar==='bar')">
<p>TEST OK!</p>
</mt:if>

<p><mt:date format="Y-m-d H:m:s T"></p>

<p><mt:var name="foo">, <mt:var name="bar">, <mt:var name="baz"></p>

<mt:loop name="loop_vars1">
<mt:if name="__first__">
  <ul>
</mt:if>
    <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
        <mt:var name="__value__" escape="1">
    </li>
<mt:if name="__last__">
  </ul>
</mt:if>
</mt:loop>

<mt:loop name="loop_vars2" sort_by="value:reverse">
<mt:if name="__first__">
  <ul>
</mt:if>
    <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
        <mt:var name="__key__"> : <mt:var name="__value__" escape="1">
    </li>
<mt:if name="__last__">
  </ul>
</mt:if>
</mt:loop>

<mt:setvars name="loop_vars3">
    4    =qux
    3    =baz
    2    =bar
    1    =foo
</mt:setvars>

<mt:loop name="loop_vars3" sort_by="key">
<mt:if name="__first__">
  <ul>
</mt:if>
    <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
        <mt:var name="__key__"> : <mt:var name="__value__" escape="1">
    </li>
<mt:if name="__last__">
  </ul>
</mt:if>
</mt:loop>
<hr>
<pre>
<mt:literal escape="1">
<mt:include file="includes/header.tpl">

<h1><mt:trans phrase="Welcome to %s!" params="PAML"></h1>

<pre>
prefix : <mt:property name="prefix">
ldelim : <mt:ldelim escape>
rdelim : <mt:rdelim escape>
</pre>

<mt:if test="($foo==='foo'&&$bar==='bar')">
<p>TEST OK!</p>
</mt:if>

<p><mt:date format="Y-m-d H:m:s T"></p>

<p><mt:var name="foo">, <mt:var name="bar">, <mt:var name="baz"></p>

<mt:loop name="loop_vars1">
<mt:if name="__first__">
  <ul>
</mt:if>
    <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
        <mt:var name="__value__" escape="1">
    </li>
<mt:if name="__last__">
  </ul>
</mt:if>
</mt:loop>

<mt:loop name="loop_vars2" sort_by="value:reverse">
<mt:if name="__first__">
  <ul>
</mt:if>
    <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
        <mt:var name="__key__"> : <mt:var name="__value__" escape="1">
    </li>
<mt:if name="__last__">
  </ul>
</mt:if>
</mt:loop>

<mt:setvars name="loop_vars3">
    4    =qux
    3    =baz
    2    =bar
    1    =foo
</mt:setvars>

<mt:loop name="loop_vars3" sort_by="key">
<mt:if name="__first__">
  <ul>
</mt:if>
    <li class="<mt:if name="__odd__">odd<mt:else>even</mt:if>">
        <mt:var name="__key__"> : <mt:var name="__value__" escape="1">
    </li>
<mt:if name="__last__">
  </ul>
</mt:if>
</mt:loop>
<mt:include file="includes/footer.tpl">
</mt:literal>
</pre>

<mt:include file="includes/footer.tpl">