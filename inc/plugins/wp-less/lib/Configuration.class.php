<?php

class WPLessConfiguration extends WPPluginToolkitConfiguration
{
  /**
   * Refers to the name of the plugin
   */
  const UNIX_NAME = 'wp-less';

  /**
   * Refers to the version of the plugin
   */
  const VERSION =   '1.4';

  /**
   * @protected
   */
  protected $variables = array();

  /**
   * @protected
   * @see http://leafo.net/lessphp/docs/index.html#custom_functions
   */
  protected $functions = array();


  protected function configure()
  {
    $this->configureOptions();
  }

  protected function configureOptions()
  {
    $this->setVariables(array());
  }

  /**
   * Set global Less variables
   * 
   * @since 1.4
   */
  public function addVariable($name, $value)
  {
    $this->variables[$name] = $value;
  }

  /**
   * Returns the registered variables
   * 
   * @since 1.4
   * @return array
   */
  public function getVariables()
  {
    return $this->variables;
  }

  /**
   * Set global Less variables
   * 
   * @since 1.4
   */
  public function setVariables(array $variables)
  {
    $this->variables = $variables;
  }

  /**
   * Return LESS functions
   * 
   * @since 1.4.2
   * @return array
   */
  public function getFunctions()
  {
    return $this->functions;
  }

  /**
   * Registers a new LESS function
   * 
   * @param string $name
   * @param Closure|function $callback
   * @param array $scope CSS handles to limit callback registration to (if empty, applies to every stylesheet) – not used yet
   * @see http://leafo.net/lessphp/docs/index.html#custom_functions
   */
  public function registerFunction($name, $callback, $scope = array())
  {
    $this->functions[$name] = array(
      'callback' => $callback,
      'scope' => $scope,
    );
  }

  /**
   * Unregisters a LESS function
   * 
   * @see http://leafo.net/lessphp/docs/index.html#custom_functions
   */
  public function unregisterFunction($name)
  {
    unset($this->functions[$name]);
  }
}
