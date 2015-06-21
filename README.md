# silverstripe-webpack
Experimental webpack integration with silverstripe templates.

## Usage

### Bundle
Dump the entire javascript or stylesheet bundle.

```
$Webpack.Javascript
$Webpack.StyleSheet
```

You can also manually iterate over the bundle assets using ``Bundle``. 

```
<%  loop $Webpack.Bundle %>
    $Link
<% end_loop %>
```


### Assets
Inject assets into the bundle using ``Asset``. The path is relative to the theme directory.

```
$Webpack.Asset('./source/images/foo.png').Link
$Webpack.Asset('./source/images/foo.svg').Content
```

You will need to require the injected assets from your entry. This file is saved into the source directory by default.

```
// ./source/main.js
require('./silverstripe_template_require.js');
```

### Configuration
#### Defaults

```
Webpack:
  build: 'assets'
  source: 'source'
  manifestName: 'manifest.json'
  injectedName: 'silverstripe_template_require.js'
  developmentServer: false
```