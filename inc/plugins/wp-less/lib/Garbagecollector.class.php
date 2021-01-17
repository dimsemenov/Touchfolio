<?php

class WPLessGarbagecollector
{
    /**
     * @static
     * @var int Max number of compiled versions of a same file to keep
     */
    public static $COMPILED_VERSIONS = 3;

    /**
     * @protected
     * @var WPLessConfiguration
     */
    protected $configuration;

	public function __construct(WPLessConfiguration $configuration)
	{
		$this->configuration = $configuration;
	}

	/**
	 * Performs the cleanup of outdated CSS files
	 *
	 */
	public function clean()
	{
		$outdated_files = $this->getOutdatedFiles($this->configuration->getTtl());
    $this->filterVersions($outdated_files);

		if (!empty($outdated_files))
		{
			$this->deleteFiles($outdated_files);
		}
	}

	/**
	 * Retrieves old CSS files and list them
	 *
	 * @param $ttl int
	 * @return array
	 */
	protected function getOutdatedFiles($ttl)
	{
		$outdated = array();
		$time = time();
		$dir = new RecursiveDirectoryIterator($this->configuration->getUploadDir());
        $dir->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);

		/*
		 * Collecting CSS files
		 */
		$files = new RegexIterator(
			new RecursiveIteratorIterator($dir),
			'#.css#U',
			RecursiveRegexIterator::ALL_MATCHES
		);

		/*
		 * Checking expiry
		 */
		foreach ($files as $filepath => $match)
		{
			(filemtime($filepath) + $ttl < $time) ? array_push($outdated, $filepath) : null;
		}

		return $outdated;
	}

    protected function filterVersions(array &$outdated_files)
    {
        $groups = array();
        $keep = array();

        // Grouping and collecting data
        foreach ($outdated_files as $index => &$file)
        {
            $m = array();
            preg_match_all('#^(?P<group>.+)-(?P<token>[^\-\.]+).css$#sU', $file, $m, PREG_SET_ORDER);

            if (empty($m))
            {
                continue;
            }

            if (!isset($groups[ $m['group'] ]))
            {
                $groups[ $m['group'] ] = array();
            }

            $groups[ $m['group'] ][ $file ] = filemtime($file);
        }

        // Capping groups
        foreach ($groups as &$versions)
        {
            arsort($versions);
            $chunks = array_chunk($versions, self::$COMPILED_VERSIONS, true);

            if (!empty($chunks[0]))
            {
                $keep = array_merge($keep, $chunks[0]);
            }
        }

        // Returning the diff
        $outdated_files = array_diff($outdated_files, array_flip($keep));
    }

	/**
	 * Remove a bunch of files
	 *
	 * @protected
	 * @param array $files
	 * @return array
	 */
	protected function deleteFiles(array $files)
	{
		return array_map('unlink', $files);
	}
}
