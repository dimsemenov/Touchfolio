# Common Usage

Once installed, **the plugin is ready to work**. All you have to do is to define which LESS files you want to automatically compile into static CSS.

The advantage over the less.js embed in the browser is purely a matter of **performance and caching**.

The default values of `wp-less` **already take care about server overhead**, browser cache-hit issues and settings change!

## Registering a LESS stylesheet

Just like you would add a CSS stylesheet in Wordpress using Wordpress' built-in function [`wp_enqueue_style`](http://codex.wordpress.org/Function_Reference/wp_enqueue_style) you can now also add your LESS stylesheets using this function. This function can only be called upon in your theme's [`functions.php`](http://codex.wordpress.org/Theme_Development#Functions_File). Register your LESS stylesheets as following:

```php
add_action('init', 'theme_enqueue_styles');

function theme_enqueue_styles() {
	wp_enqueue_style('theme-main', get_stylesheet_directory_uri().'/stylesheets/theme-main.less');
	wp_enqueue_style('theme-extra', get_stylesheet_directory_uri().'/stylesheets/theme-extra.less');
}
```

This will compile the specified files into CSS files which will be put in a separate folder belonging to the plugin. HTML code linking to them is then automatically added to your main page by adding `<link>` tags when Wordpress calls `wp_head()`.

Note that you can still use `wp_enqueue_style` for CSS files as well, compilation will just be skipped then.

## Configuration Constants

Default configuration may not suit your needs so a few constants are available.

You should alter them in your regular `wp-config.php` file, under the root of your WordPress install.

### Compilation Strategy

```php
// wp-config.php

define('WP_LESS_COMPILATION', '<value:string>');
```

`<value:string>` can be replaced by the following values:

* **deep** (default): LESS stylesheets will be recompiled if the file **or** imported files have been modified;
* **always**: LESS stylesheets will be recompiled on *every page*, even if they have not been altered;
* **legacy**: LESS stylesheets will be recompiled **only** if the registered stylesheet has been modified, regardless of imported files (it was the default behavior prior to `wp-less 1.5`).

**NOTICE**: The `always` strategy compiles stylesheets on every page, every time. It can cause serious server overhead with high volumes of traffic and LESS code.

### Always Recompile

Sometimes, you want to always recompile, whatever the reasons are. **This is not a recommended *production* setting**.

You should also have in mind these 2 things:

1. this value is equivalent to `define(‘WP_LESS_COMPILATION’, ‘always’);`;
1. this value overrides the setting defined by `WP_LESS_COMPILATION `.

```php
// wp-config.php

define('WP_LESS_ALWAYS_RECOMPILE', <value:bool>);
```

`<value:bool>` can be replaced by the following values:

* `true`: all LESS stylesheets will be recompiled on each visited page on your blog;
* `false`(default): stylesheets are not recompiled on each visited page.

### WP_DEBUG

If `WP_DEBUG` is set to true, LESS stylesheets will be recompiled on each visited page of your blog.

It is the same behavior as `WP_LESS_ALWAYS_RECOMPILE` except you also gets all WordPress debug stuff with it.

## Available variables

Since version `1.5`, `wp-less` setup default variables useable in every LESS stylesheet (even imported ones):

### `@stylesheet_directory_uri`

> Since 1.5

Equals to the value returned by [`get_stylesheet_directory_uri()`](http://codex.wordpress.org/get_stylesheet_directory_uri).

It is particularly useful if you want to link to assets located in your stylesheet theme directory.

### `@template_directory_uri`

> Since 1.5

Equals to the value returned by [`get_template_directory_uri()`](http://codex.wordpress.org/get_template_directory_uri).

It is particularly useful if you want to link to assets located in your template theme directory.
