{% include file="includes/header.tpl" %}

<h1>{{ trans phrase="Welcome to %s!" params="PAML" }}</h1>

<pre>
prefix : {{ property name="prefix" }}
ldelim : {{ ldelim escape="1" }}
rdelim : {{ rdelim escape="1" }}
</pre>

<p>{{ date format="Y-m-d H:m:s T" }}</p>

{% if test="($foo==='foo'&&$bar==='bar')" %}
<p>TEST OK!</p>
{% endif %}

<p>{{ foo }}, {{ bar }}, {{ baz }}</p>

{% foreach from="$loop_vars1" %}
{% if name="__first__" %}
  <ul>
{% endif %}
    <li class="{% if name="__odd__" %}odd{% else %}even{% endif %}">
        {{ __value__ escape="1" }}
    </li>
{% if name="__last__" %}
  </ul>
{% endif %}
{% endforeach %}

{% foreach from="$loop_vars2" sort_by="value:reverse" %}
{% if name="__first__" %}
  <ul>
{% endif %}
    <li class="{% if name="__odd__" %}odd{% else %}even{% endif %}">
        {{ __key__ }} : {{ __value__ escape="1" }}
    </li>
{% if name="__last__" %}
  </ul>
{% endif %}
{% endforeach %}

{% assignvars name="loop_vars3" %}
    4    =qux
    3    =baz
    2    =bar
    1    =foo
{% endassignvars %}

{% foreach from="$loop_vars3" sort_by="key" %}
{% if name="__first__" %}
  <ul>
{% endif %}
    <li class="{% if name="__odd__" %}odd{% else %}even{% endif %}">
        {{ __key__ }} : {{ __value__ escape="1" }}
    </li>
{% if name="__last__" %}
  </ul>
{% endif %}
{% endforeach %}
<hr>
<pre>
{% literal %}
&#123;&#37;&#32;&#105;&#110;&#99;&#108;&#117;&#100;&#101;&#32;&#102;&#105;&#108;&#101;&#61;&#34;&#105;&#110;&#99;&#108;&#117;&#100;&#101;&#115;&#47;&#104;&#101;&#97;&#100;&#101;&#114;&#46;&#116;&#112;&#108;&#34;&#32;&#37;&#125;

&#60;&#104;&#49;&#62;&#123;&#123;&#32;&#116;&#114;&#97;&#110;&#115;&#32;&#112;&#104;&#114;&#97;&#115;&#101;&#61;&#34;&#87;&#101;&#108;&#99;&#111;&#109;&#101;&#32;&#116;&#111;&#32;&#37;&#115;&#33;&#34;&#32;&#112;&#97;&#114;&#97;&#109;&#115;&#61;&#34;&#80;&#65;&#77;&#76;&#34;&#32;&#125;&#125;&#60;&#47;&#104;&#49;&#62;

&#60;&#112;&#114;&#101;&#62;
&#112;&#114;&#101;&#102;&#105;&#120;&#32;&#58;&#32;&#123;&#123;&#32;&#112;&#114;&#111;&#112;&#101;&#114;&#116;&#121;&#32;&#110;&#97;&#109;&#101;&#61;&#34;&#112;&#114;&#101;&#102;&#105;&#120;&#34;&#32;&#125;&#125;
&#108;&#100;&#101;&#108;&#105;&#109;&#32;&#58;&#32;&#123;&#123;&#32;&#108;&#100;&#101;&#108;&#105;&#109;&#32;&#101;&#115;&#99;&#97;&#112;&#101;&#61;&#34;&#49;&#34;&#32;&#125;&#125;
&#114;&#100;&#101;&#108;&#105;&#109;&#32;&#58;&#32;&#123;&#123;&#32;&#114;&#100;&#101;&#108;&#105;&#109;&#32;&#101;&#115;&#99;&#97;&#112;&#101;&#61;&#34;&#49;&#34;&#32;&#125;&#125;
&#60;&#47;&#112;&#114;&#101;&#62;

&#60;&#112;&#62;&#123;&#123;&#32;&#100;&#97;&#116;&#101;&#32;&#102;&#111;&#114;&#109;&#97;&#116;&#61;&#34;&#89;&#45;&#109;&#45;&#100;&#32;&#72;&#58;&#109;&#58;&#115;&#32;&#84;&#34;&#32;&#125;&#125;&#60;&#47;&#112;&#62;

&#123;&#37;&#32;&#105;&#102;&#32;&#116;&#101;&#115;&#116;&#61;&#34;&#40;&#36;&#102;&#111;&#111;&#61;&#61;&#61;&#39;&#102;&#111;&#111;&#39;&#38;&#38;&#36;&#98;&#97;&#114;&#61;&#61;&#61;&#39;&#98;&#97;&#114;&#39;&#41;&#34;&#32;&#37;&#125;
&#60;&#112;&#62;&#84;&#69;&#83;&#84;&#32;&#79;&#75;&#33;&#60;&#47;&#112;&#62;
&#123;&#37;&#32;&#101;&#110;&#100;&#105;&#102;&#32;&#37;&#125;

&#60;&#112;&#62;&#123;&#123;&#32;&#102;&#111;&#111;&#32;&#125;&#125;&#44; &#123;&#123;&#32;&#98;&#97;&#114;&#32;&#125;&#125;&#44; &#123;&#123;&#32;&#98;&#97;&#122;&#32;&#125;&#125;&#60;&#47;&#112;&#62;

&#123;&#37;&#32;&#102;&#111;&#114;&#101;&#97;&#99;&#104;&#32;&#102;&#114;&#111;&#109;&#61;&#34;&#36;&#108;&#111;&#111;&#112;&#95;&#118;&#97;&#114;&#115;&#49;&#34;&#32;&#37;&#125;
&#123;&#37;&#32;&#105;&#102;&#32;&#110;&#97;&#109;&#101;&#61;&#34;&#95;&#95;&#102;&#105;&#114;&#115;&#116;&#95;&#95;&#34;&#32;&#37;&#125;
&#32;&#32;&#60;&#117;&#108;&#62;
&#123;&#37;&#32;&#101;&#110;&#100;&#105;&#102;&#32;&#37;&#125;
&#32;&#32;&#32;&#32;&#60;&#108;&#105;&#32;&#99;&#108;&#97;&#115;&#115;&#61;&#34;&#123;&#37;&#32;&#105;&#102;&#32;&#110;&#97;&#109;&#101;&#61;&#34;&#95;&#95;&#111;&#100;&#100;&#95;&#95;&#34;&#32;&#37;&#125;&#111;&#100;&#100;&#123;&#37;&#32;&#101;&#108;&#115;&#101;&#32;&#37;&#125;&#101;&#118;&#101;&#110;&#123;&#37;&#32;&#101;&#110;&#100;&#105;&#102;&#32;&#37;&#125;&#34;&#62;
&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#123;&#123;&#32;&#95;&#95;&#118;&#97;&#108;&#117;&#101;&#95;&#95;&#32;&#101;&#115;&#99;&#97;&#112;&#101;&#61;&#34;&#49;&#34;&#32;&#125;&#125;
&#32;&#32;&#32;&#32;&#60;&#47;&#108;&#105;&#62;
&#123;&#37;&#32;&#105;&#102;&#32;&#110;&#97;&#109;&#101;&#61;&#34;&#95;&#95;&#108;&#97;&#115;&#116;&#95;&#95;&#34;&#32;&#37;&#125;
&#32;&#32;&#60;&#47;&#117;&#108;&#62;
&#123;&#37;&#32;&#101;&#110;&#100;&#105;&#102;&#32;&#37;&#125;
&#123;&#37;&#32;&#101;&#110;&#100;&#102;&#111;&#114;&#101;&#97;&#99;&#104;&#32;&#37;&#125;

&#123;&#37;&#32;&#102;&#111;&#114;&#101;&#97;&#99;&#104;&#32;&#102;&#114;&#111;&#109;&#61;&#34;&#36;&#108;&#111;&#111;&#112;&#95;&#118;&#97;&#114;&#115;&#50;&#34;&#32;&#115;&#111;&#114;&#116;&#95;&#98;&#121;&#61;&#34;&#118;&#97;&#108;&#117;&#101;&#58;&#114;&#101;&#118;&#101;&#114;&#115;&#101;&#34;&#32;&#37;&#125;
&#123;&#37;&#32;&#105;&#102;&#32;&#110;&#97;&#109;&#101;&#61;&#34;&#95;&#95;&#102;&#105;&#114;&#115;&#116;&#95;&#95;&#34;&#32;&#37;&#125;
&#32;&#32;&#60;&#117;&#108;&#62;
&#123;&#37;&#32;&#101;&#110;&#100;&#105;&#102;&#32;&#37;&#125;
&#32;&#32;&#32;&#32;&#60;&#108;&#105;&#32;&#99;&#108;&#97;&#115;&#115;&#61;&#34;&#123;&#37;&#32;&#105;&#102;&#32;&#110;&#97;&#109;&#101;&#61;&#34;&#95;&#95;&#111;&#100;&#100;&#95;&#95;&#34;&#32;&#37;&#125;&#111;&#100;&#100;&#123;&#37;&#32;&#101;&#108;&#115;&#101;&#32;&#37;&#125;&#101;&#118;&#101;&#110;&#123;&#37;&#32;&#101;&#110;&#100;&#105;&#102;&#32;&#37;&#125;&#34;&#62;
&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#123;&#123;&#32;&#95;&#95;&#107;&#101;&#121;&#95;&#95;&#32;&#125;&#125;&#32;&#58;&#32;&#123;&#123;&#32;&#95;&#95;&#118;&#97;&#108;&#117;&#101;&#95;&#95;&#32;&#101;&#115;&#99;&#97;&#112;&#101;&#61;&#34;&#49;&#34;&#32;&#125;&#125;
&#32;&#32;&#32;&#32;&#60;&#47;&#108;&#105;&#62;
&#123;&#37;&#32;&#105;&#102;&#32;&#110;&#97;&#109;&#101;&#61;&#34;&#95;&#95;&#108;&#97;&#115;&#116;&#95;&#95;&#34;&#32;&#37;&#125;
&#32;&#32;&#60;&#47;&#117;&#108;&#62;
&#123;&#37;&#32;&#101;&#110;&#100;&#105;&#102;&#32;&#37;&#125;
&#123;&#37;&#32;&#101;&#110;&#100;&#102;&#111;&#114;&#101;&#97;&#99;&#104;&#32;&#37;&#125;

&#123;&#37;&#32;&#97;&#115;&#115;&#105;&#103;&#110;&#118;&#97;&#114;&#115;&#32;&#110;&#97;&#109;&#101;&#61;&#34;&#108;&#111;&#111;&#112;&#95;&#118;&#97;&#114;&#115;&#51;&#34;&#32;&#37;&#125;
&#32;&#32;&#32;&#32;&#52;&#32;&#32;&#32;&#32;&#61;&#113;&#117;&#120;
&#32;&#32;&#32;&#32;&#51;&#32;&#32;&#32;&#32;&#61;&#98;&#97;&#122;
&#32;&#32;&#32;&#32;&#50;&#32;&#32;&#32;&#32;&#61;&#98;&#97;&#114;
&#32;&#32;&#32;&#32;&#49;&#32;&#32;&#32;&#32;&#61;&#102;&#111;&#111;
&#123;&#37;&#32;&#101;&#110;&#100;&#97;&#115;&#115;&#105;&#103;&#110;&#118;&#97;&#114;&#115;&#32;&#37;&#125;

&#123;&#37;&#32;&#102;&#111;&#114;&#101;&#97;&#99;&#104;&#32;&#102;&#114;&#111;&#109;&#61;&#34;&#36;&#108;&#111;&#111;&#112;&#95;&#118;&#97;&#114;&#115;&#51;&#34;&#32;&#115;&#111;&#114;&#116;&#95;&#98;&#121;&#61;&#34;&#107;&#101;&#121;&#34;&#32;&#37;&#125;
&#123;&#37;&#32;&#105;&#102;&#32;&#110;&#97;&#109;&#101;&#61;&#34;&#95;&#95;&#102;&#105;&#114;&#115;&#116;&#95;&#95;&#34;&#32;&#37;&#125;
&#32;&#32;&#60;&#117;&#108;&#62;
&#123;&#37;&#32;&#101;&#110;&#100;&#105;&#102;&#32;&#37;&#125;
&#32;&#32;&#32;&#32;&#60;&#108;&#105;&#32;&#99;&#108;&#97;&#115;&#115;&#61;&#34;&#123;&#37;&#32;&#105;&#102;&#32;&#110;&#97;&#109;&#101;&#61;&#34;&#95;&#95;&#111;&#100;&#100;&#95;&#95;&#34;&#32;&#37;&#125;&#111;&#100;&#100;&#123;&#37;&#32;&#101;&#108;&#115;&#101;&#32;&#37;&#125;&#101;&#118;&#101;&#110;&#123;&#37;&#32;&#101;&#110;&#100;&#105;&#102;&#32;&#37;&#125;&#34;&#62;
&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#123;&#123;&#32;&#95;&#95;&#107;&#101;&#121;&#95;&#95;&#32;&#125;&#125;&#32;&#58;&#32;&#123;&#123;&#32;&#95;&#95;&#118;&#97;&#108;&#117;&#101;&#95;&#95;&#32;&#101;&#115;&#99;&#97;&#112;&#101;&#61;&#34;&#49;&#34;&#32;&#125;&#125;
&#32;&#32;&#32;&#32;&#60;&#47;&#108;&#105;&#62;
&#123;&#37;&#32;&#105;&#102;&#32;&#110;&#97;&#109;&#101;&#61;&#34;&#95;&#95;&#108;&#97;&#115;&#116;&#95;&#95;&#34;&#32;&#37;&#125;
&#32;&#32;&#60;&#47;&#117;&#108;&#62;
&#123;&#37;&#32;&#101;&#110;&#100;&#105;&#102;&#32;&#37;&#125;
&#123;&#37;&#32;&#101;&#110;&#100;&#102;&#111;&#114;&#101;&#97;&#99;&#104;&#32;&#37;&#125;

&#123;&#37;&#32;&#105;&#110;&#99;&#108;&#117;&#100;&#101;&#32;&#102;&#105;&#108;&#101;&#61;&#34;&#105;&#110;&#99;&#108;&#117;&#100;&#101;&#115;&#47;&#102;&#111;&#111;&#116;&#101;&#114;&#46;&#116;&#112;&#108;&#34;&#32;&#37;&#125;
{% endliteral %}
</pre>

{% include file="includes/footer.tpl" %}