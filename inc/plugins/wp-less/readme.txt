=== WP-LESS ===
Contributors: fabrizim,oncletom
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=752034
Tags: dev, theme, themes, toolkit, plugin-toolkit, less, lesscss, lessc, lessphp, productivity, style, stylesheet, api
Requires at least: 3.2
Tested up to: 4.3
Stable tag: trunk

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

= Documentation =

Advanced topics on how to use the plugin API are [available on the Github project documentation](https://github.com/oncletom/wp-less/tree/master/doc).

= Requirements =

The sole requirement is to use WordPress API and LESS convention: the `.less` extension.

**Minimal Requirements**: PHP 5.3 and WordPress 3.2.
**Relies on**: [Less.php](http://lessphp.gpeasy.com/), [plugin-toolkit](http://wordpress.org/extend/plugins/plugin-toolkit/).

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

== Upgrade Notice ==

= 1.6.0 =

Warning: this release has some breaking changes. If you the old selector expression syntax (eg. Bootstrap 2.3) then it’s recommended to use 0.3.9.

 * Add support for ; as argument delimiter
 * Add support for passing arguments by name to mixin
 * Remove old selector expression syntax ("hello")
 * Remove ability to skip arguments by repeating delimiter
 * Add built in functions: sin, cos, tan, asin, acos, atan, pow, pi, mod, sqrt, extract
 * Fix bug where @arguments was not getting values from ...
 * Selector interpolation works inside of brackets in selector
 * Fix bug when resolving mixin that has same name as enclosing class
 * Duplicate properties are now removed from output

== Changelog ==

= Version 1.8.0 =

 * feature: default less compiler is now [oyejorge/less.php](http://lessphp.gpeasy.com/) ([#90](https://github.com/oncletom/wp-less/pull/68))

= Version 1.7.6 =

 * info: updated vendored lessphp libraries versions (oyejorge/less.php@1.7.0.5)

= Version 1.7.5 =

 * feature: now works properly in the admin side ([#68](https://github.com/oncletom/wp-less/pull/68))

= Version 1.7.4 =

 * feature: favour composer autoload to manual PHP `require` ([#64](https://github.com/oncletom/wp-less/pull/64))

= Version 1.7.3 =

 * bug: fixed the LESS library loading ([#63](https://github.com/oncletom/wp-less/issues/63))
 * doc: documented the new LESS library swapping

= Version 1.7.0 =

 * feature: ability to provide your own flavour of `lessphp` or `less.php` ([#53](https://github.com/oncletom/wp-less/pull/53))
 * bug: fixed stylesheet directory computation ([#61](https://github.com/oncletom/wp-less/pull/61))
 * bug: unlink exception during utpdated files cleanup ([#49](https://github.com/oncletom/wp-less/pull/49))
 * style: code cleanup ([#56](https://github.com/oncletom/wp-less/pull/56), [#55](https://github.com/oncletom/wp-less/pull/55))

= Version 1.6.0 =

Read the UPGRADE NOTICE carefully as this release contains BC change. Hence the version bump to `1.6.0`.

 * lessphp: updated to v0.4.0

= Version 1.5.4 =

 * bug: fixed stylesheet URL computation ([#38](https://github.com/oncletom/wp-less/pull/38))
 * bug: fixed cache-hit miss after stylesheet garbage collection ([#40](https://github.com/oncletom/wp-less/pull/40))
 * added a CONTRIBUTORS file

= Version 1.5.3 =

 * lessphp: updated to v0.3.9

= Version 1.5.2 =

 * bug: fixed garbage collector bug ([#28](https://github.com/oncletom/wp-less/pull/28))
 * bug: fixed cachebusting URI generation in deep mode ([#29](https://github.com/oncletom/wp-less/pull/29))
 * bug: fixed access to Plugin instance, matching the documentation ([#39](https://github.com/oncletom/wp-less/pull/39))

= Version 1.5.1 =

 * feature(beta): less stylesheets can be enqueued in `wp-admin`
 * feature: added `WPLessConfiguration::getTtl` method to let you configure the delay of old-files cleanup
 * bug: fixed automatic replacements with absolute and data uri ([#19](https://github.com/oncletom/wp-less/pull/19))
 * bug: fixed garbage collector; was pruning active stylesheets even if too old (buggy with active cache) ([#20](https://github.com/oncletom/wp-less/pull/20))

= Version 1.5 =

Mostly issues related to `lessphp` 0.3.8 features.

 * /!\ Leveraged PHP Minimum Version to 5.2.4 /!\ ([WordPress already asks you the same](http://wordpress.org/about/requirements/))
 * [dev documentation available online](https://github.com/oncletom/wp-less/tree/master/doc)
 * bug: stylesheets compilation is now processed on `wp_enqueue_scripts` ([prop of @RixTox](https://github.com/oncletom/wp-less/pull/18))
 * feature: providing stylesheet and template directory uri variables (`@stylesheet_directory_uri` & `@template_directory_uri`) following WordPress convention
 * feature: Pruning old compiled files [#15](https://github.com/oncletom/wp-less/pull/15)
 * feature: Smarter LESS compilation (following @import file updates) [#13](https://github.com/oncletom/wp-less/pull/13)
 * feature: Systematic LESS rebuild through configuration [#14](https://github.com/oncletom/wp-less/pull/14)
 * improvement: Match lessphp variable API [#12](https://github.com/oncletom/wp-less/pull/12)

= Version 1.4.3 =

 * bug: fixed HTTPS/Networked Blog URL replacement ([#8](https://github.com/oncletom/wp-less/pull/8), [#9](https://github.com/oncletom/wp-less/pull/9))
 * bug: fixed the `property of non-object in Plugin.class.php` bug
 * lessphp: updated to 0.3.8 (compatible with lessjs 1.3)

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

Lots of efforts have been done to write a [consistent documentation](https://github.com/oncletom/wp-less/tree/master/doc)
to address issues you may encounter.

It covers topics like path customization, declaring LESS variables from PHP, creating new LESS functions etc.

== Upgrade Notice ==

= 1.5 =

Some changes in the API may breaks compatibility with your PHP code dealing with `wp-less`.

Please [open issues](https://github.com/oncletom/wp-less/issues) and describe your technical problems [if the usage is not documented](https://github.com/oncletom/wp-less/tree/master/doc).

= 1.4 =

As `lessphp` has been upgraded to `0.3.0`, its behavior changed a little bit.

Please check your LESS syntax [according to the document](http://leafo.net/lessphp/docs/) before applying this update.

== Screenshots ==

1. Sample of LESS to CSS conversion.
