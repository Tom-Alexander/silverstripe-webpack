# silverstripe-webpack
Experimental webpack integration with silverstripe templates.

## Usage

### Bundle
Dump all the javascript or stylesheet assets.

```
$Webpack.Javascript
$Webpack.StyleSheet
```

``Assets`` can be used to retrieve all the assets across all the chunks. You can also filter
this list by file extension.

```
<%  loop $Webpack.Assets %>
    $Link
<% end_loop %>

<%  loop $Webpack.Assets('css') %>
    $Link
<% end_loop %>

```

Use ``Chunks`` to iterate over the chunks. You can filter this list by chunk name.
```
<%  loop $Webpack.Chunks %>
    <!-- Chunk: $Name -->
    <% loop $Assets %>
      <!-- $Link -->
      $Tag
    <% end_loop %>
<% end_loop %>

<%  loop $Webpack.Chunks('main') %>
    <!-- Chunk: $Name -->
    <% loop $Assets %>
      $Tag
    <% end_loop %>
<% end_loop %>

```

### Assets
Inject assets into the bundle using ``Asset``. The path is relative to the source directory.

```
$Webpack.Asset('./images/foo.png').Link
$Webpack.Asset('./images/foo.svg').Content
```

You will need to require the assets from your entry. This file is saved into the source directory.

```
// ./source/main.js
require('./silverstripe_template_require.js');
```

### Configuration
#### Defaults

```
Webpack:
  build:  'assets'
  source: 'source'
  manifest: 'manifest.json'
  development_server: false
  entry: 'silverstripe_template_require.js'
```