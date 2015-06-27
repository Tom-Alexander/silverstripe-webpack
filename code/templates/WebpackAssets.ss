<% loop $Assets %><% if $Extension == 'js' %>
    <script type="text/javascript" src="$Link"></script><% end_if %><% end_loop %>
<% loop $Assets %><% if $Extension == 'css' %>
    <link rel="stylesheet" type="text/css" href="$Link"/><% end_if %><% end_loop %>