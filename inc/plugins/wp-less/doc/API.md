# API

## Processing Workflow

This is part of the [WordPress Action Refererence](http://codex.wordpress.org/Plugin_API/Action_Reference).

1. `wp-less_compiler_construct_pre` WP-LESS action
1. `wp-less_compiler_construct` WP-LESS filter
1. `wp-less_init` WP-LESS action
1. …
1. `plugin_loaded` WordPress action
1. `after_setup_theme` WordPress action
1. `init` WordPress action
1. …
1. `wp` WordPress event
1. …
1. `wp_head` WordPress action
1. `wp-less_plugin_process_stylesheets` WP-LESS action during `wp_enqueue_scripts`
1. Then, for each stylesheet:
	1. `wp-less_stylesheet_construct` WP-LESS action
	1. `wp-less_stylesheet_compute_target_path` WP-LESS filter
	1. `wp-less_stylesheet_save_pre` WP-LESS action (if it has to compile)
	1. `wp-less_stylesheet_save` WP-LESS filter (it it has to compile)
	1. `wp-less_stylesheet_save_post` WP-LESS action (it it has to compile)
1. …
1. `wp_print_styles` WordPress action

This workflow means if you have to alter some configuration values, it has to be done before `wp` priority 999.

It also means if you register stylesheets **after** `wp` action, they won’t be handled by the plugin.

## Plugin Hooks

## `WPLessPlugin` Class

You can access to the known instance of `WPLessPlugin` at any time by doing the following:

```php
$less = WPLessPlugin::getInstance();

// do stuff with its API like:
$less->addVariable('red', '#f00');
```

### `addVariable`

TBD.

### `setVariables`

TBD.

### `registerFunction`

TBD.

### `unregisterFunction`

TBD.

### `addImportDir`

TBD.

### `setImportDir`

TBD.

### `getImportDir`

TBD.

### `install`

TBD.

### `uninstall`

TBD.

### `processStylesheet`

TBD.

### `processStylesheets`

TBD.

## `WPLessConfiguration` Class

You can access to the known instance of `WPLessConfiguration ` at any time by doing the following:

```php
$config = WPLessPlugin::getInstance()->getConfiguration();

// do stuff with its API like:
$config->setCompilationStrategy('legacy');
$config->getTtl();	// returns 432000 (5 days)
```

### `getConfigurationStrategy`

TBD.

### `alwaysRecompile`

TBD.

### `setConfigurationStrategy`

TBD.

### `getTtl`

TBD.

## Scheduled Tasks

TBD.
