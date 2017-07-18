<paml:include file="includes/header.tpl">

<h1><paml:trans phrase="Welcome to %s!" params="PAML"></h1>

<pre>
prefix : <paml:property name="prefix">
ldelim : <paml:ldelim escape>
rdelim : <paml:rdelim escape>
</pre>

<paml:if test="( $foo === 'foo' )">
<p>TEST OK!</p>
</paml:if>

<p><paml:date format="Y-m-d H:m:s T"></p>

<p><paml:var name="foo">, <paml:var name="bar">, <paml:var name="baz"></p>

<paml:foreach from="loop_vars1">
<paml:if name="__first__">
  <ul>
</paml:if>
    <li class="<paml:if name="__odd__">odd<paml:else>even</paml:if>">
        <paml:var name="__value__" escape="1">
    </li>
<paml:if name="__last__">
  </ul>
</paml:if>
</paml:foreach>

<paml:foreach from="loop_vars2" sort_by="value:reverse">
<paml:if name="__first__">
  <ul>
</paml:if>
    <li class="<paml:if name="__odd__">odd<paml:else>even</paml:if>">
        <paml:var name="__key__"> : <paml:var name="__value__" escape="1">
    </li>
<paml:if name="__last__">
  </ul>
</paml:if>
</paml:foreach>

<paml:assignvars name="loop_vars3">
    4    =qux
    3    =baz
    2    =bar
    1    =foo
</paml:assignvars>

<paml:foreach from="loop_vars3" sort_by="key">
<paml:if name="__first__">
  <ul>
</paml:if>
    <li class="<paml:if name="__odd__">odd<paml:else>even</paml:if>">
        <paml:var name="__key__"> : <paml:var name="__value__" escape="1">
    </li>
<paml:if name="__last__">
  </ul>
</paml:if>
</paml:foreach>
<hr>
<pre>
<paml:literal escape="1">
<paml:include file="includes/header.tpl">

<h1><paml:trans phrase="Welcome to %s!" params="PAML"></h1>

<pre>
prefix : <paml:property name="prefix">
ldelim : <paml:ldelim escape>
rdelim : <paml:rdelim escape>
</pre>

<paml:if test="( $foo === 'foo' )">
<p>TEST OK!</p>
</paml:if>

<p><paml:date format="Y-m-d H:m:s T"></p>

<p><paml:var name="foo">,<paml:var name="bar">,<paml:var name="baz"></p>

<paml:foreach from="loop_vars1">
<paml:if name="__first__">
  <ul>
</paml:if>
    <li class="<paml:if name="__odd__">odd<paml:else>even</paml:if>">
        <paml:var name="__value__" escape="1">
    </li>
<paml:if name="__last__">
  </ul>
</paml:if>
</paml:foreach>

<paml:foreach from="loop_vars2" sort_by="value:reverse">
<paml:if name="__first__">
  <ul>
</paml:if>
    <li class="<paml:if name="__odd__">odd<paml:else>even</paml:if>">
        <paml:var name="__key__"> : <paml:var name="__value__" escape="1">
    </li>
<paml:if name="__last__">
  </ul>
</paml:if>
</paml:foreach>

<paml:assignvars name="loop_vars3">
    4    =qux
    3    =baz
    2    =bar
    1    =foo
</paml:assignvars>

<paml:foreach from="loop_vars3" sort_by="key">
<paml:if name="__first__">
  <ul>
</paml:if>
    <li class="<paml:if name="__odd__">odd<paml:else>even</paml:if>">
        <paml:var name="__key__"> : <paml:var name="__value__" escape="1">
    </li>
<paml:if name="__last__">
  </ul>
</paml:if>
</paml:foreach>

<paml:include file="includes/footer.tpl">
</paml:literal>
</pre>

<paml:include file="includes/footer.tpl">