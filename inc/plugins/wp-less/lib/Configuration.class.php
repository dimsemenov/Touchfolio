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
  const VERSION =   '1.8.0';



	/**
	 * Current compilation strategy
	 *
	 * @since 1.5
	 * @protected
	 * @var string
	 */
	protected $compilation_strategy = 'deep';

	/**
	 * Available compilation strategies
	 *
	 * @since 1.5
	 * @var array
	 */
	protected $compilation_strategies = array('legacy', 'always', 'deep');

	/**
	 * Time to live before pruning CSS cache
	 *
	 * @protected
	 * @var int delay in seconds
	 */
	protected $ttl = 432000;    // 5 days

  protected function configure()
  {
    $this->configureOptions();
  }

  protected function configureOptions()
  {
	  if (defined('WP_LESS_COMPILATION') && WP_LESS_COMPILATION)
	  {
		  $this->setCompilationStrategy(WP_LESS_COMPILATION);
	  }

	  //previous setting can be overridden for special reasons (dev/prod for example)
	  if ((defined('WP_DEBUG') && WP_DEBUG) || (defined('WP_LESS_ALWAYS_RECOMPILE') && WP_LESS_ALWAYS_RECOMPILE))
	  {
		  $this->setCompilationStrategy('always');
	  }
  }

	/**
	 * Current compilation strategy
	 *
	 * @api
	 * @since 1.5
	 * @return string Active compilation strategy
	 */
	public function getCompilationStrategy()
	{
		return $this->compilation_strategy;
	}

	/**
	 * Always recompile
	 *
	 * @since 1.5
	 * @return bool
	 */
	public function alwaysRecompile()
	{
		return $this->compilation_strategy === 'always';
	}

	/**
	 * Set compilation strategy
	 *
	 * @api
	 * @since 1.5
	 * @param $strategy string Actual compilation "strategy"
	 */
	public function setCompilationStrategy($strategy)
	{
		if (!in_array($strategy, $this->compilation_strategies))
		{
			throw new WPLessException('Unknown compile strategy: ['.$strategy.'] provided.');
		}

		$this->compilation_strategy = $strategy;
	}

	/**
	 * Retrieves the TTL of a compiled CSS file
	 *
	 * @api
	 * @since 1.5
	 * @return int Time to live of a compiled CSS file
	 */
	public function getTtl()
	{
		return $this->ttl;
	}

    /**
     * Sets the TTL fo a compiled CSS file
     *
     * @api
     * @param $ttl
     * @since 1.5.1
     */
    public function setTtl($ttl)
    {
        $this->ttl = (int)$ttl;
    }

}
