# Embedding within a WordPress Theme

For some reasons, you want to bundle `wp-less` plugin within a theme or a plugin. 2 things to know:

1. it’s totally feasible and it’s officially supported;
1. theme bundling will take over plugin bundling, or even the official plugin.

## Basics of embedding

`wp-less` plugin provides a special file for embedding. It does the dirty job and let you the hand on what to do before dispatching stuff.

### First: embedding
The first part of embedding is… embedding:

```php
// wp-content/themes/your-theme/functions.php

require dirname(__FILE__) . '/vendor/wp-less/bootstrap-for-theme.php';
```

At this point, the plugin is available these way:
* `$WPLessPlugin` variable available within the global scope;
* `WPLessPlugin::getInstance()` will always return you the active plugin instance, whatever the scope is (longer but safer).

### Second: configuring

You can do whatever you want with the plugin. It hasn’t been  initialized yet. Deal with folders, change compilation strategy or whatever.

You can register any LESS stylesheet, it has no incidence.  
There won’t be parsed at this moment.

### Third: initializing

You now have to plug `wp-less` on WordPress events to make the plugin work.

This is as simple as the following code:

```php
// wp-content/themes/your-theme/functions.php

$less = WPLessPlugin::getInstance();
$less->dispatch();
```

The `dispatch` method deals with everything you need.

You’re done!

## Manual registration of scheduled tasks

However, by embedding the plugin, you have to manually activate the garbage collector. This feature cleans every compiled file older than 5 days, every day.

Regarding of where you embed the plugin, you have to **register the task only once**:

```php
// wp-content/themes/your-theme/functions.php
// …

$less->install();
```

The same way, you also have to unregister the garbage collector at the relevant moment:

```php
// wp-content/themes/your-theme/functions.php
// …

$less->uninstall();
```

Within a plugin, it would looks like this:

```php
// wp-content/themes/your-theme/functions.php
// …

register_activation_hook(__FILE__, array($less, 'install'));
register_deactivation_hook(__FILE__, array($less, 'uninstall'));
```

## If the plugin is installed aside

Remember that if `wp-less` exists in the `wp-content/plugins` folder, you can use this code as dependency of your theme or plugin.

To always rely on the latest up-to-date version, you should detect the existence of the plugin **after all plugins have been loaded**. You could then bundle your own `wp-less` copy as legacy fallback, just in case.

```php
// wp-content/themes/my-theme/functions.php

add_action('plugins_loaded', 'register_less_fallback');

function register_less_fallback(){
	if (!class_exists('WPLessPlugin')){
		require dirname(__FILE__) . '/vendor/wp-less/bootstrap-for-theme.php';
		WPLessPlugin::getInstance()->dispatch();
		// we’re done, everything works as if the plugin is activated
	}
}
```
