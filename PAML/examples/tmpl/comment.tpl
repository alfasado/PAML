<!--include file="includes/header.tpl"-->

<h1><!--trans phrase="Welcome to %s!" params="PAML"--></h1>

<pre>
prefix : <!--property name="prefix"-->
ldelim : <!--ldelim escape-->
rdelim : <!--rdelim escape-->
</pre>

<!--if test="($foo==='foo'&&$bar==='bar')"-->
<p>TEST OK!</p>
<!--/if-->

<p><!--date format="Y-m-d H:m:s T"--></p>

<p><!--var name="foo"-->, <!--var name="bar"-->, <!--var name="baz"--></p>

<!--loop name="$loop_vars1"-->
<!--if name="__first__"-->
  <ul>
<!--/if-->
    <li class="<!--if name="__odd__"-->odd<!--else-->even<!--/if-->">
        <!--var name="__value__" escape="1"-->
    </li>
<!--if name="__last__"-->
  </ul>
<!--/if-->
<!--/loop-->

<!--loop name="$loop_vars2" sort_by="value:reverse"-->
<!--if name="__first__"-->
  <ul>
<!--/if-->
    <li class="<!--if name="__odd__"-->odd<!--else-->even<!--/if-->">
        <!--var name="__key__"--> : <!--var name="__value__" escape="1"-->
    </li>
<!--if name="__last__"-->
  </ul>
<!--/if-->
<!--/loop-->

<!--setvars name="loop_vars3"-->
    4    =qux
    3    =baz
    2    =bar
    1    =foo
<!--/setvars-->

<!--loop name="$loop_vars3" sort_by="key"-->
<!--if name="__first__"-->
  <ul>
<!--/if-->
    <li class="<!--if name="__odd__"-->odd<!--else-->even<!--/if-->">
        <!--var name="__key__"--> : <!--var name="__value__" escape="1"-->
    </li>
<!--if name="__last__"-->
  </ul>
<!--/if-->
<!--/loop-->
<hr>
<pre>
<!--literal escape="1"-->
<!--include file="includes/header.tpl"-->

<h1><!--trans phrase="Welcome to %s!" params="PAML"--></h1>

<pre>
prefix : <!--property name="prefix"-->
ldelim : <!--ldelim escape-->
rdelim : <!--rdelim escape-->
</pre>

<!--if test="($foo==='foo'&&$bar==='bar')"-->
<p>TEST OK!</p>
<!--/if-->

<p><!--date format="Y-m-d H:m:s T"--></p>

<p><!--var name="foo"-->, <!--var name="bar"-->, <!--var name="baz"--></p>

<!--loop name="$loop_vars1"-->
<!--if name="__first__"-->
  <ul>
<!--/if-->
    <li class="<!--if name="__odd__"-->odd<!--else-->even<!--/if-->">
        <!--var name="__value__" escape="1"-->
    </li>
<!--if name="__last__"-->
  </ul>
<!--/if-->
<!--/loop-->

<!--loop name="$loop_vars2" sort_by="value:reverse"-->
<!--if name="__first__"-->
  <ul>
<!--/if-->
    <li class="<!--if name="__odd__"-->odd<!--else-->even<!--/if-->">
        <!--var name="__key__"--> : <!--var name="__value__" escape="1"-->
    </li>
<!--if name="__last__"-->
  </ul>
<!--/if-->
<!--/loop-->

<!--setvars name="loop_vars3"-->
    4    =qux
    3    =baz
    2    =bar
    1    =foo
<!--/setvars-->

<!--loop name="$loop_vars3" sort_by="key"-->
<!--if name="__first__"-->
  <ul>
<!--/if-->
    <li class="<!--if name="__odd__"-->odd<!--else-->even<!--/if-->">
        <!--var name="__key__"--> : <!--var name="__value__" escape="1"-->
    </li>
<!--if name="__last__"-->
  </ul>
<!--/if-->
<!--/loop-->
<!--include file="includes/footer.tpl"-->
<!--/literal-->
</pre>
<!--include file="includes/footer.tpl"-->