<?php
/**
 * Stylesheet management
 *
 * @author oncletom
 * @package wp-less
 * @subpackage lib
 */
class WPLessStylesheet
{
  protected $compiler,
            $stylesheet;

  protected $is_new = true,
            $signature,
            $source_path,
            $source_timestamp,
            $source_uri,
            $target_path,
            $target_uri;

  public static $upload_dir,
                $upload_uri;

  /**
   * Constructs the object, paths and all
   *
   * @author oncletom
   * @since 1.0
   * @version 1.1
   * @throws WPLessException if something is not properly configured
   * @param _WP_Dependency $stylesheet
   * @param array $variables
   */
  public function __construct(_WP_Dependency $stylesheet, array $variables = array())
  {
    $this->stylesheet = $stylesheet;

    if (!self::$upload_dir || !self::$upload_uri)
    {
      throw new WPLessException('You must configure `upload_dir` and `upload_uri` static attributes before constructing this object.');
    }

    $this->stylesheet->ver = null;
    $this->configurePath();
    $this->configureSignature($variables);

    if (file_exists($this->getTargetPath()))
    {
      $this->is_new = false;
    }

    do_action('wp-less_stylesheet_construct', $this);
  }

  /**
   * Returns the computed path for a given dependency
   *
   * @author oncletom
   * @since 1.0
   * @version 1.0
   * @return string
   */
  public function computeTargetPath()
  {
    $target_path = preg_replace('#^'.get_theme_root_uri().'#U', '', $this->stylesheet->src);
    $target_path = preg_replace('/.less$/U', '', $target_path);

    $target_path .= '-%s.css';

    return apply_filters('wp-less_stylesheet_compute_target_path', $target_path);
  }

  /**
   * Configure paths for the stylesheet
   * Since this moment, everything is configured to be usable
   *
   * @protected
   * @author oncletom
   * @since 1.0
   * @version 1.1
   */
  protected function configurePath()
  {
    $target_file =          $this->computeTargetPath();

    $this->source_path =    WP_CONTENT_DIR.preg_replace('#^'.content_url().'#U', '', $this->stylesheet->src);
    $this->source_uri =     $this->stylesheet->src;
    $this->target_path =    self::$upload_dir.$target_file;
    $this->target_uri =     self::$upload_uri.$target_file;

    $this->setSourceTimestamp(filemtime($this->source_path));
  }

  /**
   * Configures the file signature
   *
   * It corresponds to a unique hash taking care of file name, timestamp and variables.
   * It should be called each time stylesheet variables are updated.
   *
   * @author oncletom
   * @param array $variables List of variables used for signature
   * @since 1.4.2
   * @version 1.1
   */
  protected function configureSignature(array $variables = array())
  {
    $this->signature = substr(sha1(serialize($variables) . $this->computeTargetPath() . $this->source_timestamp), 0, 10);
  }

  /**
   * Returns source content (CSS to parse)
   *
   * @author oncletom
   * @since 1.0
   * @version 1.0
   * @return string
   */
  public function getSourceContent()
  {
    return apply_filters('wp-less_stylesheet_source_content', file_get_contents($this->source_path));
  }

  /**
   * Returns source path
   *
   * @author oncletom
   * @since 1.0
   * @version 1.0
   * @return string
   */
  public function getSourcePath()
  {
    return $this->source_path;
  }

  /**
   * Returns source URI
   *
   * @author oncletom
   * @since 1.0
   * @version 1.0
   * @return string
   */
  public function getSourceUri()
  {
    return $this->source_uri;
  }

  /**
   * Returns target path
   *
   * @author oncletom
   * @since 1.0
   * @version 1.0
   * @return string
   */
  public function getTargetPath()
  {
    return sprintf($this->target_path, $this->signature);
  }

  /**
   * Returns target URI
   *
   * @author oncletom
   * @since 1.0
   * @version 1.1
   * @param boolean $append_version
   * @param string  $version_prefix
   * @return string
   */
  public function getTargetUri()
  {
    return sprintf($this->target_uri, $this->signature);
  }

  /**
   * Tells if compilation is needed
   *
   * @author oncletom
   * @since 1.0
   * @version 1.3
   * @return boolean
   */
  public function hasToCompile()
  {
    return $this->is_new;
  }

  /**
   * Save the current stylesheet as a parsed css file
   *
   * @deprecated
   * @see WPLessCompiler::saveStylesheet()
   */
  public function save()
  {
    $this->is_new = false;
  }

  /**
   * Sets the source timestamp of the file
   * Mostly used to generate a proper cache busting URI
   *
   * @since 1.5.2
   * @param integer $timestamp
   */
  public function setSourceTimestamp($timestamp)
  {
    $this->source_timestamp = $timestamp;
  }
}
