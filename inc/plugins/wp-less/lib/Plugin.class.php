<?php
if (!class_exists('WPPluginToolkitPlugin')) {
    require dirname(__FILE__) . '/vendor/plugin-toolkit/BasePlugin.class.php';
}

/**
 * WP LESS Plugin class
 *
 * @author oncletom
 * @package wp-less
 * @subpackage lib
 */
class WPLessPlugin extends WPPluginToolkitPlugin
{
    /**
     * @protected
     * @var bool
     */
    protected $is_filters_registered = false;

    /**
     * @protected
     * @var bool
     */
    protected $is_hooks_registered = false;

    /**
     * @protected
     * @var null|WPLessCompiler
     */
    protected $compiler = null;

    /**
     * @static
     * @var Pattern used to match stylesheet files to process them as pure CSS
     */
    public static $match_pattern = '/\.less$/U';

    public function __construct(WPLessConfiguration $configuration)
    {
        parent::__construct($configuration);
    }

    public function instantiateCompiler()
    {
        if (!class_exists('lessc')) {
            // Load the parent compiler class
            require $this->getLessCompilerPath();
        }

        $this->compiler = new WPLessCompiler;
        $this->compiler->setVariable('stylesheet_directory_uri', "'" . get_stylesheet_directory_uri() . "'");
        $this->compiler->setVariable('template_directory_uri', "'" . get_template_directory_uri() . "'");
    }

    /**
     * Load the parent compiler class. This is provided via lessc.inc.php for
     * both the lessphp and less.php implementations
     *
     * @author  fabrizim
     * @since   1.7.1
     *
     */
    protected function getLessCompilerPath()
    {
        // The usage of the WP_LESS_COMPILER is a holdover from an older implentation
        // of this opt-in functionality
        $compiler = defined('WP_LESS_COMPILER') ? WP_LESS_COMPILER : apply_filters('wp_less_compiler', 'less.php');

        switch( $compiler ){
            case 'less.php':
                return dirname(__FILE__).'/../vendor/oyejorge/less.php/lessc.inc.php';
            case 'lessphp':
                return dirname(__FILE__).'/../vendor/leafo/lessphp/lessc.inc.php';
            default:
                return $compiler;
        }
    }

    public function getCompiler()
    {
        if( $this->compiler ) return $this->compiler;
        $this->instantiateCompiler();
        return $this->compiler;
    }

    /**
     * Dispatches all events of the plugin
     *
     * @author  oncletom
     * @since   1.3
     */
    public function dispatch()
    {
        if ($this->is_hooks_registered) {
            return false;
        }

        /*
         * Garbage Collection Registration
         */
        $gc = new WPLessGarbagecollector($this->configuration);
        add_action('wp-less-garbage-collection', array($gc, 'clean'));

        /*
         * Last Hooks
         */
        $this->registerHooks();
    }

    /**
     * Performs plugin install actions
     *
     * @since 1.5
     */
    public function install()
    {
        /*
         * Check to see if it isn't scheduled first, for example
         * this would occur when loaded via theme
         */
        if ( FALSE === wp_get_schedule( 'wp-less-garbage-collection' ) )
        {
            wp_schedule_event(time(), 'daily', 'wp-less-garbage-collection');
        }

        /*
         * Clear old hooks, prior to hook change
         * #57
         */
        wp_clear_scheduled_hook( 'wp-less_garbage_collection' );
    }

    /**
     * Performs plugin uninstall actions
     *
     * @since 1.5
     */
    public function uninstall()
    {
        wp_clear_scheduled_hook('wp-less-garbage-collection');
    }

    /**
     * Correct Stylesheet URI
     *
     * It enables the cache without loosing reference to URI
     *
     * @author oncletom
     * @since 1.2
     * @version 1.2
     * @param string $css parsed CSS
     * @param WPLessStylesheet Stylesheet currently processed
     * @return string parsed and fixed CSS
     */
    public function filterStylesheetUri($css, WPLessStylesheet $stylesheet)
    {
        $this->_TmpBaseDir = dirname($stylesheet->getSourceUri());

        return preg_replace_callback(
            '#url\s*\((?P<quote>[\'"]{0,1})(?P<url>[^\'"\)]+)\1\)#siU',
            array($this, '_filterStylesheetUri'),
            $css
        );

        unset($this->_TmpBaseDir);
    }

    /**
     * Returns a proper url() CSS key with absolute paths if needed
     *
     * @protected
     * @param array $matches Expects at least 0, 'uri' and 'quote' keys
     * @return string
     */
    protected function _filterStylesheetUri($matches)
    {
        if (preg_match('#^(http|@|data:|/)#Ui', $matches[2])) {
            return $matches[0];
        }

        return sprintf('url(%s%s%1$s)',
            $matches[1],
            $this->_TmpBaseDir . '/' . $matches[2]
        );
    }

    /**
     * Find any style to process
     *
     * @author oncletom
     * @since 1.0
     * @version 1.0
     * @return array styles to process
     */
    protected function getQueuedStylesToProcess()
    {
        $wp_styles = $this->getStyles();
        $to_process = array();

        foreach ((array)$wp_styles->queue as $style_id) {
            if (preg_match(self::$match_pattern, $wp_styles->registered[$style_id]->src)) {
                $to_process[] = $style_id;
            }
        }

        return apply_filters('wp-less_get_queued_styles_to_process', $to_process);
    }

    /**
     * Returns WordPress Styles manager
     *
     * @author oncletom
     * @uses WP_Styles
     * @since 1.0
     * @version 1.1
     * @return WP_Styles styles instance
     */
    public function getStyles()
    {
        global $wp_styles;

        //because if someone never registers through `wp_(enqueue|register)_stylesheet`,
        //$wp_styles is never initialized, and thus, equals NULL
        return null === $wp_styles || !$wp_styles instanceof WP_Styles ? new WP_Styles() : $wp_styles;
    }

    /**
     * Process a single stylesheet
     *
     * @author oncletom
     * @since 1.1
     * @version 1.3
     * @param string $handle
     * @param $force boolean If set to true, rebuild all stylesheets, without considering they are updated or not
     * @return WPLessStylesheet
     */
    public function processStylesheet($handle, $force = false)
    {
        $force = !!$force ? $force : $this->configuration->alwaysRecompile();

        $wp_styles = $this->getStyles();
        $stylesheet = new WPLessStylesheet($wp_styles->registered[$handle], $this->getCompiler()->getVariables());

        if ($this->configuration->getCompilationStrategy() === 'legacy' && $stylesheet->hasToCompile()) {
            $this->getCompiler()->saveStylesheet($stylesheet);
        } elseif ($this->configuration->getCompilationStrategy() !== 'legacy') {
            $this->getCompiler()->cacheStylesheet($stylesheet, $force);
        }

        $wp_styles->registered[$handle]->src = $stylesheet->getTargetUri();

        return $stylesheet;
    }

    /**
     * Process all stylesheets to compile just in time
     *
     * @author oncletom
     * @since 1.0
     * @version 1.1
     * @param $force boolean If set to true, rebuild all stylesheets, without considering they are updated or not
     */
    public function processStylesheets($force = false)
    {
        $styles = $this->getQueuedStylesToProcess();
        $force = is_bool($force) && $force ? !!$force : false;

        WPLessStylesheet::$upload_dir = $this->configuration->getUploadDir();
        WPLessStylesheet::$upload_uri = $this->configuration->getUploadUrl();

        if (empty($styles)) {
            return;
        }

        if (!wp_mkdir_p(WPLessStylesheet::$upload_dir)) {
            throw new WPLessException(sprintf('The upload dir folder (`%s`) is not writable from %s.', WPLessStylesheet::$upload_dir, get_class($this)));
        }

        foreach ($styles as $style_id) {
            $this->processStylesheet($style_id, $force);
        }

        do_action('wp-less_plugin_process_stylesheets', $styles);
    }

    /**
     * Compile editor stylesheets registered via add_editor_style()
     *
     * @param  string $mce_css Comma separated list of CSS file URLs
     * @return string $mce_css New comma separated list of CSS file URLs
     */
    public function processEditorStylesheets($mce_css) {

        if( !$mce_css ) return $mce_css;

        // extract CSS file URLs
        $style_sheets = explode( ",", $mce_css );

        if ( count( $style_sheets ) ) {
            $compiled_css = array();

            // loop through editor styles, any .less files will be compiled and the compiled URL returned
            foreach( $style_sheets as $style_sheet ) {

                // Remove version from uri
                $parts = parse_url( $style_sheet );
                $style_sheet = $parts['scheme'] . '://' . $parts['host'] . $parts['path'];

                // Get extension and set handle for wp_register_style()
                $pathinfo = pathinfo($style_sheet);
                $extension = $pathinfo['extension'];
                $handle = $pathinfo['filename'];

                // Only process less files
                if( $extension === 'less' ) {

                    // Register stylesheet as wp dependency
                    wp_register_style( $handle, $style_sheet, array(), null );

                    // Process stylesheet
                    $stylesheet = $this->processStylesheet($handle, false);

                    // Add if successfull
                    if($stylesheet) {
                        $compiled_css[] = $stylesheet->getTargetUri();
                    }

                }

                else {
                    $compiled_css[] = $style_sheet;
                }
            }

            $mce_css = implode( ",", $compiled_css );
        }

        // return new URLs
        return $mce_css;
    }

    /**
     * Method to register hooks (and do it only once)
     *
     * @protected
     * @author oncletom
     * @since 1.1
     * @version 1.1
     */
    protected function registerHooks()
    {
        if ($this->is_hooks_registered) {
            return false;
        }

        is_admin() ? do_action('wp-less_init_admin', $this) : do_action('wp-less_init', $this);
        add_action('wp_enqueue_scripts', array($this, 'processStylesheets'), PHP_INT_MAX, 0);
        add_action('admin_enqueue_scripts', array($this, 'processStylesheets'), PHP_INT_MAX, 0);
        add_action('login_enqueue_scripts', array($this, 'processStylesheets'), PHP_INT_MAX, 0);
        add_filter('mce_css', array($this, 'processEditorStylesheets'), PHP_INT_MAX);
        add_filter('wp-less_stylesheet_save', array($this, 'filterStylesheetUri'), 10, 2);

        return $this->is_hooks_registered = true;
    }

    /**
     * Proxy method
     *
     * @see http://leafo.net/lessphp/docs/#setting_variables_from_php
     * @since 1.4
     */
    public function addVariable($name, $value)
    {
        $this->getCompiler()->setVariables(array($name => $value));
    }

    /**
     * Proxy method
     *
     * @see http://leafo.net/lessphp/docs/#setting_variables_from_php
     * @since 1.4
     */
    public function setVariables(array $variables)
    {
        $this->getCompiler()->setVariables($variables);
    }

    /**
     * Proxy method
     *
     * @see http://leafo.net/lessphp/docs/#custom_functions
     * @since 1.4.2
     */
    public function registerFunction($name, $callback)
    {
        $this->getCompiler()->registerFunction($name, $callback);
    }

    /**
     * Proxy method
     *
     * @see lessc::unregisterFunction()
     * @since 1.4.2
     */
    public function unregisterFunction($name)
    {
        $this->getCompiler()->unregisterFunction($name);
    }

    /**
     * Proxy method
     *
     * @see WPLessCompiler::getImportDir()
     * @return array
     * @since 1.5.0
     */
    public function getImportDir()
    {
        return $this->getCompiler()->getImportDir();
    }

    /**
     * Proxy method
     *
     * @see lessc::addImportDir()
     * @param string $dir
     * @since 1.5.0
     */
    public function addImportDir($dir)
    {
        $this->getCompiler()->addImportDir($dir);
    }

    /**
     * Proxy method
     *
     * @see lessc::setImportDir()
     * @param array $dirs
     * @since 1.5.0
     */
    public function setImportDir($dirs)
    {
        $this->getCompiler()->setImportDir($dirs);
    }
}
