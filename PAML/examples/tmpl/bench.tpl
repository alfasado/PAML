<html>
  <head>
    <title>
        <paml:getvar name="title" escape="1">
    </title>
  </head>
  <body>
    <h1><paml:getvar name="title" escape="1"></h1>
<paml:for loop="1000" setvar="foo">
<paml:loop name="contacts">
<p>
  name: <paml:getvar name="name"><br />
  home: <paml:getvar name="home"><br />
  cell: <paml:getvar name="cell"><br />
  e-mail: <paml:getvar name="email">
</p>
</paml:loop>

</paml:for>

<paml:var name="foo">

  </body>
</html>