<paml:include file="includes/header.tpl">

<h1><paml:hello value="World" add="!"></h1>

<paml:block indent="10">
<pre>
indent="10" 4+5=<paml:math equation="x + y" x="4" y="5"></p></pre>
</paml:block>

<paml:ppap>
<paml:if name="__first__">
  <ul>
</paml:if>
    <li class="<paml:if name="__odd__">odd<paml:else>even</paml:if>">
        <paml:var name="__counter__"> : <paml:var name="__value__" escape="1">
    </li>
<paml:if name="__last__">
  </ul>
</paml:if>
</paml:ppap>
<hr>
<pre>
<paml:literal escape="1">
<paml:include file="includes/header.tpl">

<h1><paml:hello value="World" add="!"></h1>

<paml:block indent="10">
<pre>
indent="10" 4+5=<paml:math equation="x + y" x="4" y="5"></p></pre>
</paml:block>

<paml:ppap>
<paml:if name="__first__">
  <ul>
</paml:if>
    <li class="<paml:if name="__odd__">odd<paml:else>even</paml:if>">
        <paml:var name="__counter__"> : <paml:var name="__value__" escape="1">
    </li>
<paml:if name="__last__">
  </ul>
</paml:if>
</paml:ppap>

<paml:include file="includes/footer.tpl">
</paml:literal>
</pre>
<paml:include file="includes/footer.tpl">