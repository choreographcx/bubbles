<?php

use InstagramFeed\Admin\SBI_Support;
use InstagramFeed\Admin\SBI_Support_Tool;
use InstagramFeed\Builder\SBI_Feed_Builder;
use InstagramFeed\SB_Instagram_Data_Encryption;

if (!defined('ABSPATH')) {
	return;
}

$role_id = SBI_Support_Tool::$plugin . SBI_Support_Tool::$role;
$cap = $role_id;

if (!current_user_can($cap)) {
	return;
}

$all_connected_accounts = SB_Instagram_Connected_Account::get_all_connected_accounts();
$personal_media_fields = 'id,username,media_type,media_url,thumbnail_url,caption,timestamp,permalink,children';
$basic_media_fields = 'media_url,thumbnail_url,caption,id,media_type,timestamp,username,comments_count,like_count,permalink,children';
$business_media_fields = 'id,username,media_product_type,media_url,thumbnail_url,caption,timestamp,comments_count,like_count,permalink,children';
$default_checked = 'id,username,media_type,media_product_type,timestamp,permalink,media_url,caption';
$default_checked = explode(',', $default_checked);

$feeds_list = SBI_Feed_Builder::get_feed_list();

?>
	<div class="sbi_support_tools_wrap">
		<div class="sbi-support-tool-tab">
			<button class="sbi-support-tool-tablinks active" onclick="openTab(event, 'ConnectedAccounts')">
				<span><?php esc_html_e('Connected Accounts', 'instagram-feed'); ?></span>
			</button>
			<button class="sbi-support-tool-tablinks" onclick="openTab(event, 'Hashtags')">
				<span><?php esc_html_e('Hashtags', 'instagram-feed'); ?></span>
			</button>
			<button class="sbi-support-tool-tablinks" onclick="openTab(event, 'Feeds')">
				<span><?php esc_html_e('Feeds', 'instagram-feed'); ?></span>
			</button>
			<button class="sbi-support-tool-tablinks" onclick="openTab(event, 'SystemInfo')">
				<span><?php esc_html_e('System Info', 'instagram-feed'); ?></span>
			</button>
		</div>

		<div id="ConnectedAccounts" class="sbi-support-tool-tabcontent active">
			<div class="sbi_support_tools_field_group">
				<?php if (empty($all_connected_accounts)) : ?>
					<p><?php esc_html_e('No connected accounts found.', 'instagram-feed'); ?></p>

				<?php else : ?>
					<p><?php esc_html_e('Below is a list of all connected accounts. Click the "Get Account Info" button to retrieve the account info. Click the "Get Media" button to retrieve the media for the account.', 'instagram-feed'); ?></p>

					<p class="sbi-api-notes">
						<strong><?php esc_html_e('Note: For Hashtags, the media_url is not returned for Carousel Album media type.', 'instagram-feed'); ?></strong>
					</p>

					<div class="sb-srcs-item">
						<?php foreach ($all_connected_accounts as $connected_account) :
							$connect_type = isset($connected_account['connect_type']) ? $connected_account['connect_type'] : 'personal';
							$account_type = $connect_type === 'business_basic' ? 'Business Basic' : ($connect_type === 'business_advanced' ? 'Business Advanced' : 'Personal');
							?>
							<div class="sbi-fb-srcs-item-ins">
								<?php if (!empty($connected_account['profile_picture'])) : ?>
									<div class="sb-srcs-item-avatar">
										<img src="<?php echo esc_url($connected_account['profile_picture']); ?>"
											 height="42" width="42"
											 alt="<?php echo esc_attr($connected_account['username']); ?>">
									</div>
								<?php endif; ?>

								<div class="sb-srcs-item-info">
									<h3><?php echo esc_html($connected_account['username']); ?></h3>
									<strong><?php esc_html_e('ID', 'instagram-feed') ?>:</strong>
									<span><?php echo esc_html($connected_account['id']); ?></span><br>
									<strong><?php esc_html_e('Account Type', 'instagram-feed'); ?>:</strong>
									<span><?php echo esc_html($account_type); ?></span><br><br>
								</div>

								<div class="sb-srcs-item-actions">
									<button class="button sbi-get-account-info"
											data-user-id="<?php echo esc_attr($connected_account['id']); ?>"
											data-account-type="<?php echo esc_attr($connected_account['account_type']) ?>"
											data-connect-type="<?php echo esc_attr($connect_type); ?>"><?php esc_html_e('Get Account Info', 'instagram-feed'); ?></button>

									<button class="button sbi-get-media"
											data-user-id="<?php echo esc_attr($connected_account['id']); ?>"
											data-account-type="<?php echo esc_attr($connected_account['account_type']) ?>">
											<?php /* translators: %s: Account type */
											echo sprintf(esc_html__('Get Media (%s)', 'instagram-feed'), $account_type); ?>
										</button>

									<?php if ($connected_account['account_type'] === 'business') : ?>
										<button class="button sbi-get-stories"
												data-user-id="<?php echo esc_attr($connected_account['id']); ?>"
												data-account-type="<?php echo esc_attr($connected_account['account_type']) ?>"><?php esc_html_e('Get Stories', 'instagram-feed'); ?></button>

										<button class="button sbi-get-tagged-posts"
												data-user-id="<?php echo esc_attr($connected_account['id']); ?>"
												data-account-type="<?php echo esc_attr($connected_account['account_type']) ?>"><?php esc_html_e('Get Tagged Posts', 'instagram-feed'); ?></button>

										<button class="button sbi-get-recently-searched-hashtags"
												data-user-id="<?php echo esc_attr($connected_account['id']); ?>"
												data-account-type="<?php echo esc_attr($connected_account['account_type']) ?>"><?php esc_html_e('Get Recently Searched Hashtags', 'instagram-feed'); ?></button>

										<button class="button sbi-test-hashtags"
												data-user-id="<?php echo esc_attr($connected_account['id']); ?>"
												data-account-type="<?php echo esc_attr($connected_account['account_type']) ?>"><?php esc_html_e('Test a Hashtag', 'instagram-feed'); ?></button>
									<?php endif; ?>

									<div class="sbi-checkboxes"
										 data-user-id="<?php echo esc_attr($connected_account['id']); ?>"
										 style="display: none;">
										<?php
										$media_fields = $connect_type === 'business_basic' ? $basic_media_fields : ($connect_type === 'business_advanced' ? $business_media_fields : $personal_media_fields);
										$media_fields = explode(',', $media_fields);

										foreach ($media_fields as $media_field) {
											$media_field = trim($media_field);
											$media_label = ucwords(str_replace('_', ' ', $media_field));
											$checked = in_array($media_field, $default_checked) ? 'checked disabled' : '';
											echo '<span><input type="checkbox" name="sbi_media_fields[]" value="' . $media_field . '"' . $checked . '>' . $media_label . '</span>';
										}
										?>

										<span>
										<label
											for="sbi_post_limit"><?php esc_html_e('Post Limit', 'instagram-feed'); ?></label>
										<input type="number" name="sbi_post_limit" value="10" min="1" max="100">
									</span>

										<div class="sbi-checkbox-action-btns">
											<button class="button sbi-confirm"
													data-user-id="<?php echo esc_attr($connected_account['id']); ?>"
													data-account-type="<?php echo esc_attr($connected_account['account_type']) ?>"><?php esc_html_e('Confirm', 'instagram-feed'); ?></button>

											<button
												class="button sbi-cancel"><?php esc_html_e('Cancel', 'instagram-feed'); ?></button>
										</div>
									</div>

									<?php if ($connected_account['account_type'] === 'business') : ?>
										<div class="sbi-hashtags-inner"
											 data-user-id="<?php echo esc_attr($connected_account['id']); ?>"
											 style="display: none;">
											<input type="text" name="sbi_hashtag"
												   placeholder="<?php esc_html_e('Enter a hashtag ex - hashtag', 'instagram-feed'); ?>">

											<button class="button sbi-test-hashtags-recent"
													data-user-id="<?php echo esc_attr($connected_account['id']); ?>"
													data-account-type="<?php echo esc_attr($connected_account['account_type']) ?>"><?php esc_html_e('Most recent', 'instagram-feed'); ?></button>

											<button class="button sbi-test-hashtags-top"
													data-user-id="<?php echo esc_attr($connected_account['id']); ?>"
													data-account-type="<?php echo esc_attr($connected_account['account_type']) ?>"><?php esc_html_e('Top Rated', 'instagram-feed'); ?></button>
										</div>
									<?php endif; ?>
								</div>

								<div class="sbi-response" data-id="<?php echo esc_attr($connected_account['id']); ?>">
									<div class="sbi-response-message"></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div id="Hashtags" class="sbi-support-tool-tabcontent">
			<div class="sbi_support_tools_field_group">
				<?php
				$ids_with_accounts_option = get_option('sbi_hashtag_ids_with_connected_accounts', array());
				$encryption = new SB_Instagram_Data_Encryption();
				$json = $encryption->maybe_decrypt($ids_with_accounts_option);
				if ($json) {
					$ids_with_accounts = json_decode($json, true);
				}
				?>

				<?php if (empty($ids_with_accounts)) : ?>
					<p><?php esc_html_e('No hashtags feeds found.', 'instagram-feed'); ?></p>

				<?php else : ?>
					<p><?php esc_html_e('Below is a list of all hashtags.', 'instagram-feed'); ?></p>

					<p class="sbi-api-notes">
						<strong><?php esc_html_e('Note: For Hashtags, the media_url is not returned for Carousel Album media type.', 'instagram-feed'); ?></strong>
					</p>

					<div class="sb-srcs-item">
						<?php foreach ($ids_with_accounts as $term => $hashtag) : ?>
							<div class="sbi-fb-srcs-item-ins">
								<h3>#<?php echo esc_html($term); ?></h3>
								<strong><?php esc_html_e('Connected Account', 'instagram-feed'); ?>:</strong>
								<span><?php echo esc_html($hashtag['connected_account']['username']); ?></span><br>
								<strong><?php esc_html_e('Account Type', 'instagram-feed'); ?>:</strong>
								<span><?php esc_html_e('Business Advanced', 'instagram-feed'); ?></span><br>
								<strong><?php esc_html_e('Hashtag ID', 'instagram-feed'); ?>:</strong>
								<span><?php echo esc_html($hashtag['id']); ?></span><br><br>

								<button class="button sbi-hashtags-recent"
										data-user-id="<?php echo esc_attr($hashtag['connected_account']['user_id']); ?>"
										data-account-type="<?php echo esc_attr($hashtag['connected_account']['account_type']) ?>"
										data-hashtag-id="<?php echo esc_attr($hashtag['id']); ?>"><?php esc_html_e('Most recent', 'instagram-feed'); ?></button>

								<button class="button sbi-hashtags-top"
										data-user-id="<?php echo esc_attr($hashtag['connected_account']['user_id']); ?>"
										data-account-type="<?php echo esc_attr($hashtag['connected_account']['account_type']) ?>"
										data-hashtag-id="<?php echo esc_attr($hashtag['id']); ?>"><?php esc_html_e('Top Rated', 'instagram-feed'); ?></button>

								<div class="sbi-hashtag-response" data-id="<?php echo esc_attr($hashtag['id']); ?>">
									<div class="sbi-hashtag-response-message"></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div id="Feeds" class="sbi-support-tool-tabcontent">
			<div class="sbi_support_tools_field_group">
				<?php if (empty($feeds_list)) : ?>
					<p><?php esc_html_e('No feeds found.', 'instagram-feed'); ?></p>

				<?php else : ?>
					<p><?php esc_html_e('Below is a list of all feeds.', 'instagram-feed'); ?></p>

					<div class="sb-srcs-item">
						<?php foreach ($feeds_list as $feed) : ?>
							<div class="sbi-fb-srcs-item-ins">
								<h3><?php echo esc_html($feed['feed_name']); ?></h3>
								<strong><?php esc_html_e('Feed ID', 'instagram-feed'); ?>:</strong>
								<span><?php echo esc_html($feed['id']); ?></span><br>

								<strong><?php esc_html_e('Feed Type', 'instagram-feed'); ?>:</strong>
								<span><?php echo esc_html(ucfirst($feed['settings']['type'])); ?></span><br><br>

								<?php if (!empty($feed['settings']['sources'])) : ?>
									<strong><?php esc_html_e('Connected User Account(s)', 'instagram-feed'); ?>
										:</strong>

									<?php foreach ($feed['settings']['sources'] as $connected_account) :
										$connect_type = isset($connected_account['connect_type']) ? $connected_account['connect_type'] : 'personal';
										$account_type = $connect_type === 'business_basic' ? 'Business Basic' : ($connect_type === 'business_advanced' ? 'Business Advanced' : 'Personal');
										?>
										<div class="sbi-feeds-connected-accounts">
											<strong><?php esc_html_e('ID', 'instagram-feed'); ?>: </strong>
											<span><?php echo esc_html($connected_account['user_id']); ?></span>

											<strong><?php esc_html_e('Username', 'instagram-feed'); ?>: </strong>
											<span><?php echo esc_html($connected_account['username']); ?></span>

											<strong><?php esc_html_e('Account Type', 'instagram-feed'); ?>: </strong>
											<span><?php echo esc_html($account_type); ?></span>
										</div>
									<?php endforeach; ?>
									<br>
								<?php endif; ?>

								<?php if ($feed['settings']['type'] !== 'user' && !empty($feed['settings']['hashtag'])) : ?>
									<strong><?php esc_html_e('HashTag(s)', 'instagram-feed'); ?>:</strong>

									<div class="sbi-feeds-connected-accounts">
										<?php if (is_array($feed['settings']['hashtag'])) : ?>
											<?php foreach ($feed['settings']['hashtag'] as $hashtag) : ?>
												<span><?php echo esc_html($hashtag); ?></span>
											<?php endforeach; ?>
										<?php else : ?>
											<span><?php echo esc_html($feed['settings']['hashtag']); ?></span>
										<?php endif; ?>
									</div>
									<br>
								<?php endif; ?>

								<?php if ($feed['settings']['type'] !== 'user' && !empty($feed['settings']['tagged'])) : ?>
									<strong><?php esc_html_e('Tagged Accounts', 'instagram-feed'); ?>:</strong>

									<div class="sbi-feeds-connected-accounts">
										<?php if (is_array($feed['settings']['tagged'])) : ?>
											<?php foreach ($feed['settings']['tagged'] as $tagged) : ?>
												<strong><?php esc_html_e('User ID', 'instagram-feed'); ?>: </strong>
												<span><?php echo esc_html($tagged); ?></span>
											<?php endforeach; ?>
										<?php else : ?>
											<strong><?php esc_html_e('User ID', 'instagram-feed'); ?>: </strong>
											<span><?php echo esc_html($feed['settings']['tagged']); ?></span>
										<?php endif; ?>
									</div>
									<br>
								<?php endif; ?>

								<?php if ($feed['settings']['feed_is_moderated']) : ?>
									<strong><?php esc_html_e('This feed is moderated.', 'instagram-feed'); ?></strong>
								<?php endif; ?>

							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div id="SystemInfo" class="sbi-support-tool-tabcontent">
			<div class="sbi_support_tools_field_group">
				<p><?php esc_html_e('This information can be helpful when troubleshooting issues.', 'instagram-feed'); ?></p>
				<div class="sbi-system-info">
					<?php
					$sbi_support = new SBI_Support();
					echo $sbi_support->get_system_info();
					?>
				</div>
			</div>
		</div>
	</div>
<?php
