<?php

namespace InstagramFeed;

class SBI_Feed_Cache_Manager
{
	/**
	 * Next scheduled time for the update cron.
	 *
	 * @var false|int
	 */
	private $next_scheduled;

	/**
	 * The type of caching to use.
	 *
	 * @var string
	 */
	private $cache_type;

	/**
	 * Class constructor for SB_Instagram_Feed_Cache_Manager.
	 *
	 * @param string $update_cron The name of the cron job to update the cache.
	 * @param string $cache_type The type of cache being managed.
	 */
	public function __construct($update_cron, $cache_type)
	{
		$this->next_scheduled = wp_next_scheduled($update_cron);
		$this->cache_type = $cache_type;
	}

	/**
	 * Get the caching type for the Instagram feed.
	 *
	 * @return string The caching type ('page' or the value of cache_type).
	 */
	public function get_caching_type()
	{
		if ($this->should_fallback()) {
			return 'page';
		}

		return $this->cache_type;
	}

	/**
	 * Determines if the fallback should be used based on the next scheduled time.
	 *
	 * @return bool True if the fallback should be used, false otherwise.
	 */
	private function should_fallback()
	{
		return ($this->next_scheduled - time()) < 0;
	}
}
