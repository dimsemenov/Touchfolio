=== WP-LESS ===
Contributors: oncletom
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=752034
Tags: dev, theme, themes, toolkit, plugin-toolkit, less, lesscss, lessc, lessphp, productivity, style, stylesheet, api
Requires at least: 2.8
Tested up to: 3.3
Stable tag: 1.4.2

Implementation of LESS (Leaner CSS) in order to make themes development easier.


== Description ==
[LESS](http://lesscss.org) is a templating language based on top of CSS. It provides numerous enhancements to speed up development and make its maintenance easier.

Theme developers can even bundle the plugin without worrying about conflicts: just include the special `bootstrap-for-theme.php` and read its instructions.

= Features =

 * Variables
 * Mixins (inheritance of rules)
 * Nested Rules (write less, do more)
 * Accessors (inherit a value from a specific rule)
 * Functions (logic operations for dynamic results)

The plugin lets you concentrate on what you need: coding CSS. Everything else is handled automatically, from cache management to user delivery.  
Seriously.

= Requirements =

The sole requirement is to use WordPress API and LESS convention: the `.less` extension.

**Minimal Requirements**: PHP 5.1.2 and WordPress 2.8.  
**Relies on**: [LESSPHP 0.3.1](http://leafo.net/lessphp/), [plugin-toolkit](http://wordpress.org/extend/plugins/plugin-toolkit/).

*Notice*: in case you'd like to drop the usage of this plugin, it's safe to do it. You will just need to convert back your stylesheets to CSS.

== Installation ==

= Automatic =
 1. Search for the plugin name (`WP-LESS`)
 1. Click on the install button
 1. Activate it

= Manual =
 1. Download the latest stable archive of the plugin
 1. Unzip it in your plugin folder (by default, `wp-content/plugins`)
 1. Activate it through your WordPress plugins administration page

== Changelog ==

= Version 1.4.2 =

 * feature: if `WP_DEBUG` is set to true, compilation is done on every page
 * feature: rebuild now takes care of LESS PHP variable
 * feature: added support of [custom LESS functions](http://leafo.net/lessphp/docs/index.html#custom_functions)
 * lessphp: updated to version 0.3.1

= Version 1.4.1 =

 * bug: CSS `url()` are now properly resolved relative to the theme URL

= Version 1.4 =

 * action: `wp-less_compiler_parse_pre` now takes 3 arguments: class instance, text and variable arguments
 * action: `wp-less_stylesheet_save_pre` now takes 2 arguments: class instance and variable arguments
 * helper: added `less_add_variable` to ease manipulations from theme, if needed (the file needs to be included manually)
 * stylesheet: `getBuffer()` and `setBuffer` will be removed in 1.5 version
 * lessphp: removed the custom patch for buffer manipulation, due to built-in variable management
 * lessphp: updated to version 0.3.0

= Version 1.3.1 =

 * renamed `wp-less_compiler_parse` action to `wp-less_compiler_parse_pre` to avoid name conflicts
 * renamed `wp-less_compiler_construct` action to `wp-less_compiler_construct_pre` to avoid name conflicts
 * lessphp: patched the lib to let manipulating the buffer, and replace strings (do it at your own risks)

= Version 1.3 =

 * moved stylesheet processing from `wp_print_styles` to `wp` action
 * added new compiler actions and filters (same name each): `wp-less_compiler_construct` and `wp-less_compiler_parse`
 * added `WPLessCompiler::getBuffer()` and `WPLessCompiler::setBuffer()` method, to enables hooking on LESS content, before being compiled into CSS
 * removed `WPLessStyleseet::getTargetContent` method
 * upgraded `plugin-toolkit`
 * usage of `$WPLessPlugin->dispatch` instead of `$WPLessPlugin->registerHooks` to match the new `plugin-toolkit` signature
 * no more configuration collision if usage of multiple plugins using `plugin-toolkit`
 * lessphp: updated to [eac64a9d5a3bc3186a11c7130968388819f4c403](https://github.com/leafo/lessphp/commit/eac64a9d5a3bc3186a11c7130968388819f4c403) commit

= Version 1.2.1 =

 * fixed the case where no stylesheet is queued (no warning anymore)

= Version 1.2 =

 * added 2 new filters working on freshly transformed CSS
 * added a HTML helper to LESSify directly from templates, without queuying with `wp_enqueue_stylesheet` (can't really recommend this usage)
 * added timestamp calculation so as you can be HTTP cache-control compliant
 * documented plugin hooks and filters
 * hooked a filter to update relative paths to deal `uri` and cached file location
 * lessphp: updated to version 0.2.0

= Version 1.1 =

 * added `bootstrap-for-theme.php` to let themers bundle the plugin in their own themes
 * added `WPLessPlugin::registerHooks` methods to ease hooks activation
 * theme bootstrap will only load if the plugin is not alread activated
 * `WPLessPlugin::processStylesheets()` and `WPLessPlugin::processStylesheet()` now accepts an additional parameter to force the rebuild
 * lessphp: updated to version 0.1.6
 * plugin-toolkit: updated to version 1.1


= Version 1.0 =

 * implemented API to let you control the plugin the way you want
 * just in time compilation with static file caching
 * lessphp: bundled to version 0.1.6
 * plugin-toolkit: bundled experimental plugin development


== Frequently Asked Questions ==
= How do I transform a LESS file into CSS? =
Consider this bit of code to automatically enqueue your stylesheet from your theme (or plugin):  
`wp_enqueue_style('mytheme', get_bloginfo('template_directory').'/style.css', array(), '', 'screen, projection');`

To make it process by WP-LESS, switch to this way:  
`wp_enqueue_style('mytheme', get_bloginfo('template_directory').'/style.less', array(), '', 'screen, projection');`

You understood well: you just need to change the extension of the file.

= And if I don't use the wp_enqueue_style method? =
For the moment, it's the unique way to handle this.  
Helpers will be provided soon to include LESS files in your templates in a fluent way.

= What if a *.less file contains only pure CSS? =
Nothing special. The LESS parser is fully compliant with CSS syntax.  
It means nothing will be broken so don't worry.

= I'm a themer and I don't want to ask my users to activate this plugin =
It's a very good moto. Since the 1.1 release, there is a special bootstrap file: `bootstrap-for-theme.php`.  
Everything is prepared and documented inside, with examples and hint.

Just help yourself!

= I want to inject custom variables =
LESS PHP 0.3.0 introduced a native way to set variables from PHP.

If you initialized the class by yourself, do it this way:
`$WPLessPlugin->addVariable('@default_color', '#fff');`

If you don't manage the plugin by yourself:
`WPPluginToolkitPlugin::getInstance('WPLess')->addVariable($name, $value);`

And if you want to do that from a theme, with less code:

1. include the `wp-less/lib/helper/ThemeHelper.php` file;
1. call `less_add_variable('@default_color', '#fff')`

= I want to create a new custom LESS function =
LESS PHP 0.3.1. introducted a new way to register functions without subclassing the compiler.

If you initialized the class by yourself, do it this way:
`$WPLessPlugin->registerFunction('double', 'lessphp_double');`

If you don't manage the plugin by yourself:
`WPPluginToolkitPlugin::getInstance('WPLess')->registerFunction('double', 'lessphp_double');`

And if you want to do that from a theme, with less code:

1. include the `wp-less/lib/helper/ThemeHelper.php` file;
1. call `less_register_function('double', 'lessphp_double')`

**Notice**: in this example, we assume `lessphp_double` is a valid [PHP callback](http://php.net/manual/en/language.pseudo-types.php#language.types.callback), as stated in `lessphp` documentation.

== Upgrade Notice ==

= 1.4 =

As `lessphp` has been upgraded to `0.3.0`, its behavior changed a little bit.

Please check your LESS syntax [according to the document](http://leafo.net/lessphp/docs/) before applying this update.

== Screenshots ==

1. Sample of LESS to CSS conversion.