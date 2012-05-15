<?php
/**
 * Base plugin class to extend
 *
 * @version 1.2
 * @author  oncletom
 * @package plugin-toolkit
 */
abstract class WPPluginToolkitPlugin
{
  protected $configuration;
  protected static $autoload_configured = false;

  protected static $instances = array();

  /**
   * Dispatches the plugin hooks, filters etc.
   *
   * This is basically the first thing done when the class is created.
   *
   * @author oncletom
   * @since 1.0
   * @abstract
   */
  abstract public function dispatch();

  /**
   * Plugin constructor
   *
   * @author oncletom
   * @since 1.0
   * @version 1.0
   * @param WPPluginToolkitConfiguration $configuration
   */
  public function __construct(WPPluginToolkitConfiguration $configuration)
  {
    $this->configuration = $configuration;

    if (!self::$autoload_configured)
    {
      spl_autoload_register(array($this, 'configureAutoload'));
    }

    load_plugin_textdomain($configuration->getUnixName(), $configuration->getI18nPath(), $configuration->getI18nFromPluginPath());
    do_action($configuration->getUnixName().'_plugin_construct', $this);
  }

  /**
   * Autoloads classes for this plugin
   *
   * @author oncletom
   * @return boolean
   * @param string $className
   * @version 1.0
   * @since 1.0
   */
  public function configureAutoload($className)
  {
    $prefix = $this->configuration->getPrefix();

    if (!preg_match('/^'.$prefix.'/U', $className))
    {
      return;
    }

    $libdir = $this->configuration->getDirname().'/lib';
    $path = preg_replace('/([A-Z]{1})/U', "/$1", str_replace($prefix, '', $className)).'.class.php';

    if (file_exists($libdir.$path))
    {
      require $libdir.$path;
    }

    return false;
  }

  /**
   * WordPress plugin builder
   *
   * @author oncletom
   * @static
   * @final
   * @since 1.0
   * @version 1.1
   * @param string $baseClassName
   * @param string $baseFileName
   * @param string $singleton_identifier[optional]
   * @return $baseClassName+Plugin instance
   */
  public final static function create($baseClassName, $baseFileName, $singleton_identifier = null)
  {
    if (!class_exists('WPPluginToolkitConfiguration'))
    {
      require_once dirname(__FILE__).'/BaseConfiguration.class.php';
    }

    require_once dirname($baseFileName).'/lib/Configuration.class.php';

    $class =          $baseClassName.'Plugin';
    $configuration =  $baseClassName.'Configuration';

    list($class, $configuration) = apply_filters('plugin-toolkit_create', array($class, $configuration));

    $object = new $class(new $configuration($baseClassName, $baseFileName));

    if (!is_null($singleton_identifier) && $singleton_identifier)
    {
      call_user_func(array($class, 'setInstance'), $singleton_identifier, $object);
    }

    do_action('plugin-toolkit_create', $object);

    return $object;
  }

  /**
   * Returns the current configuration
   *
   * @author oncletom
   * @since 1.0
   * @version 1.0
   * @return WPPluginToolkitConfiguration instance
   */
  public function getConfiguration()
  {
    return $this->configuration;
  }

  /**
   * Retrieves the instance of an object
   * If no identifier is given, the first created instance is returned
   *
   * @author oncletom
   * @since 1.1
   * @version 1.0
   * @static
   * @param string $identifier [optional]
   * @return object
   */
  public static function getInstance($identifier = null)
  {
    if (is_null($identifier))
    {
      $identifier = key(self::$instances);
    }

    return isset(self::$instances[$identifier]) ? self::$instances[$identifier] : null;
  }

  /**
   * Stores an instance of a created object
   * Self storage is so good :)
   *
   * @author oncletom
   * @since 1.1
   * @version 1.0
   * @static
   * @protected
   * @param string $identifier
   * @param object $object
   */
  protected static function setInstance($identifier, $object)
  {
    self::$instances[$identifier] = $object;
  }
}
