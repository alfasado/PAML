{include file="includes/header.tpl"}

<h1>{trans phrase="Welcome to %s!" params="PAML"}</h1>

<pre>
prefix : {property name="prefix"}
ldelim : {ldelim escape="1"}
rdelim : {rdelim escape="1"}
</pre>

{if test="($foo==='foo'&&$bar==='bar')"}
<p>TEST OK!</p>
{/if}

<p>{date format="Y-m-d H:m:s T"}</p>

<p>{$foo}, {$bar}, {$baz}</p>

{foreach from="$loop_vars1"}
{if name="__first__"}
  <ul>
{/if}
    <li class="{if name="__odd__"}odd{else}even{/if}">
        {$__value__ escape="1"}
    </li>
{if name="__last__"}
  </ul>
{/if}
{/foreach}

{foreach from="$loop_vars2" sort_by="value:reverse"}
{if name="__first__"}
  <ul>
{/if}
    <li class="{if name="__odd__"}odd{else}even{/if}">
        {$__key__} : {$__value__ escape="1"}
    </li>
{if name="__last__"}
  </ul>
{/if}
{/foreach}

{assignvars name="loop_vars3"}
    4    =qux
    3    =baz
    2    =bar
    1    =foo
{/assignvars}

{foreach from="$loop_vars3" sort_by="key"}
{if name="__first__"}
  <ul>
{/if}
    <li class="{if name="__odd__"}odd{else}even{/if}">
        {$__key__} : {$__value__ escape="1"}
    </li>
{if name="__last__"}
  </ul>
{/if}
{/foreach}
<hr>
<pre>
{literal escape="1"}
{include file="includes/header.tpl"}

<h1>{trans phrase="Welcome to %s!" params="PAML"}</h1>

<pre>
prefix : {property name="prefix"}
ldelim : {ldelim escape="1"}
rdelim : {rdelim escape="1"}
</pre>

{if test="($foo==='foo'&&$bar==='bar')"}
<p>TEST OK!</p>
{/if}

<p>{date format="Y-m-d H:m:s T"}</p>

<p>{$foo}, {$bar}, {$baz}</p>

{foreach from="$loop_vars1"}
{if name="__first__"}
  <ul>
{/if}
    <li class="{if name="__odd__"}odd{else}even{/if}">
        {$__value__ escape="1"}
    </li>
{if name="__last__"}
  </ul>
{/if}
{/foreach}

{foreach from="$loop_vars2" sort_by="value:reverse"}
{if name="__first__"}
  <ul>
{/if}
    <li class="{if name="__odd__"}odd{else}even{/if}">
        {$__key__} : {$__value__ escape="1"}
    </li>
{if name="__last__"}
  </ul>
{/if}
{/foreach}

{assignvars name="loop_vars3"}
    4    =qux
    3    =baz
    2    =bar
    1    =foo
{/assignvars}

{foreach from="$loop_vars3" sort_by="key"}
{if name="__first__"}
  <ul>
{/if}
    <li class="{if name="__odd__"}odd{else}even{/if}">
        {$__key__} : {$__value__ escape="1"}
    </li>
{if name="__last__"}
  </ul>
{/if}
{/foreach}

{include file="includes/footer.tpl"}
{/literal}
</pre>

{include file="includes/footer.tpl"}