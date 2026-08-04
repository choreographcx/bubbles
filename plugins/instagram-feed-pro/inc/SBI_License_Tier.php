<?php

namespace InstagramFeed;

use InstagramFeed\Vendor\Smashballoon\Framework\Packages\License_Tier\License_Tier;

/**
 * SBI License Tier
 *
 * @since 6.3
 */
class SBI_License_Tier extends License_Tier
{
	/**
	 * License key
	 *
	 * @var string
	 */
	public $license_key_option_name = 'sbi_license_key';

	/**
	 * License status
	 *
	 * @var string
	 */
	public $license_status_option_name = 'sbi_license_status';

	/**
	 * License data
	 *
	 * @var string
	 */
	public $license_data_option_name = 'sbi_license_data';

	/**
	 * Item IDs
	 */
	public $item_id_basic = 1722826; // Item id for the basic tier.
	public $item_id_plus = 1722829; // Item id for the plus tier.
	public $item_id_elite = 1722833; // Item id for the elite tier.
	public $item_id_all_access_elite = 1724078; // This is the all access item id, no need to change.

	/**
	 * Legacy item IDs
	 */
	public $item_id_personal = 33604; // Item id for the personal tier.
	public $item_id_business = 33748; // Item id for the business tier.
	public $item_id_developer = 33751; // Item id for the developer tier.
	public $item_id_all_access = 789157; // This is the all access item id, no need to change.

	/**
	 * Tier names
	 */
	public $license_tier_basic_name = 'basic'; // Basic tier name.
	public $license_tier_plus_name = 'plus'; // Plus tier name.
	public $license_tier_elite_name = 'elite'; // Elite tier name.

	/**
	 * Legacy tier names
	 */
	public $license_tier_personal_name = 'personal'; // Personal tier name.
	public $license_tier_business_name = 'business'; // Business tier name.
	public $license_tier_developer_name = 'developer'; // Developer tier name.
	public $edd_item_name = SBI_PLUGIN_NAME;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * This defines the features list of the plugin
	 *
	 * @return void
	 */
	public function features_list()
	{
		$features_list = [
			'basic' => [
				// List of features for basic tier.
				'unlimited_pro_feeds',
				'lightbox',
				'carousel',
				'multiple_feed_layouts',
				'instagram_stories',
				'comments_likes',
				'captions',
				'performance_optimization',
				'feed_customizer',
				'downtime_prevention_system',
				'gdpr_compliant',
				'oembeds',
			],
			'plus' => [
				// List of features for plus tier.
				'visual_moderation_system',
				'filter_posts',
				'feed_templates',
				'hashtag_feeds',
				'multiple_feed_types',
			],
			'elite' => [
				// List of features for elite tier.
				'tagged_feeds',
				'shoppable_feeds',
				'feed_themes',
			],
		];

		$this->plugin_features = $features_list;
	}

	/**
	 * This defines features for legacy tiers
	 *
	 * @return void
	 */
	public function legacy_features_list()
	{
		$legacy_features = [
			'personal' => [
				// List of features for personal tier.
				'unlimited_pro_feeds',
				'lightbox',
				'carousel',
				'multiple_feed_layouts',
				'instagram_stories',
				'comments_likes',
				'captions',
				'performance_optimization',
				'feed_customizer',
				'downtime_prevention_system',
				'gdpr_compliant',
				'oembeds',
				'visual_moderation_system',
				'filter_posts',
				'feed_templates',
				'hashtag_feeds',
				'multiple_feed_types',
				'tagged_feeds',
				'shoppable_feeds',
				'feed_themes'
			],
			'business' => [
				// List of features for business tier.
			],
			'developer' => [
				// List of features for developer tier.
			],
		];

		$this->legacy_features = $legacy_features;
	}

	/**
	 * Pro features list
	 *
	 * @return array
	 */
	public function pro_features_list()
	{
		return [
			__('Display Hashtag & Tagged feeds', 'instagram-feed'),
			__('Powerful visual moderation', 'instagram-feed'),
			__('Comments and Likes', 'instagram-feed'),
			__('Highlight specific posts', 'instagram-feed'),
			__('Multiple layout options', 'instagram-feed'),
			__('Popup photo/video lightbox', 'instagram-feed'),
			__('Instagram Stories', 'instagram-feed'),
			__('Shoppable feeds', 'instagram-feed'),
			__('Pro support', 'instagram-feed'),
			__('Post captions', 'instagram-feed'),
			__('Combine multiple feed types', 'instagram-feed'),
			__('30 day money back guarantee', 'instagram-feed'),
		];
	}

	/**
	 * Plus features list
	 *
	 * @return array
	 */
	public function plus_features_list()
	{
		return [
			__('Powerful visual moderation', 'instagram-feed'),
			__('Filter posts', 'instagram-feed'),
			__('Display Hashtag feeds', 'instagram-feed'),
			__('Feed templates', 'instagram-feed'),
			__('Combine multiple feed types', 'instagram-feed'),
			__('Standard support', 'instagram-feed'),
			__('30 day money back guarantee', 'instagram-feed'),
		];
	}

	/**
	 * Elite features list
	 *
	 * @return array
	 */
	public function elite_features_list()
	{
		return [
			__('Tagged feeds', 'instagram-feed'),
			__('Shoppable feeds', 'instagram-feed'),
			__('Feed Themes', 'instagram-feed'),
			__('Priority support', 'instagram-feed'),
			__('30 day money back guarantee', 'instagram-feed'),
		];
	}
}
