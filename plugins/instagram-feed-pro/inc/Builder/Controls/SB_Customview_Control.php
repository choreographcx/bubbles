<?php

namespace InstagramFeed\Builder\Controls;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Customizer Builder
 * Custom View
 *  This control will used for custom HTMlL controls like (source, feed type...)
 *
 * @since 6.0
 */
class SB_Customview_Control extends SB_Controls_Base
{
	/**
	 * Get control type.
	 *
	 * Getting the Control Type
	 *
	 * @return string
	 * @since 6.0
	 * @access public
	 */
	public function get_type()
	{
		return 'customview';
	}

	/**
	 * Output Control
	 *
	 * @param string $controlEditingTypeModel Control Editing Type Model.
	 * @since 6.0
	 * @access public
	 */
	public function get_control_output($controlEditingTypeModel)
	{
		$this->get_control_sources_output($controlEditingTypeModel);
		$this->get_control_shoppable_disabled_output($controlEditingTypeModel);
		$this->get_control_shoppable_enabled_output($controlEditingTypeModel);
		$this->get_control_shoppable_selected_post_output($controlEditingTypeModel);
		$this->get_control_moderation_mode_output($controlEditingTypeModel);
		$this->get_control_feed_template_output($controlEditingTypeModel);
		$this->get_control_feedtheme_output($controlEditingTypeModel);
		$this->get_control_likescomments_info_output($controlEditingTypeModel);
	}

	/**
	 * Sources Output Control
	 *
	 * @param string $controlEditingTypeModel Control Editing Type Model.
	 * @since 6.0
	 * @access public
	 */
	public function get_control_sources_output($controlEditingTypeModel)
	{
		?>
		<div class="sb-control-feedtype-ctn" v-if="control.viewId == 'sources'">

			<div class="sb-control-feedtype-item sbi-fb-fs"
				 v-for="(feedType, feedTypeID) in selectSourceScreen.multipleTypes"
				 v-if="checkMultipleFeedTypeActiveCustomizer(feedTypeID)">

				<div class="sb-control-elem-label-title sbi-fb-fs">
					<div class="sb-control-elem-heading sb-small-p sb-dark-text" v-html="feedType.heading"></div>
					<div class="sb-control-elem-tltp"
						 @mouseover.prevent.default="toggleElementTooltip(feedType.description, 'show', 'center' )"
						 @mouseleave.prevent.default="toggleElementTooltip('', 'hide')">
						<div class="sb-control-elem-tltp-icon" v-html="svgIcons['info']"></div>
					</div>
				</div>

				<div class="sb-control-feedtype-list sbi-fb-fs">
					<div class="sb-control-feedtype-list-item"
						 v-for="selectedSource in returnSelectedSourcesByTypeCustomizer(feedTypeID)">
						<div class="sb-control-feedtype-list-item-icon"
							 v-html="feedTypeID == 'hashtag' ? svgIcons['hashtag'] : svgIcons['user']"></div>
						<span v-html="feedTypeID == 'hashtag' ? selectedSource : selectedSource.username"></span>
					</div>
				</div>

			</div>

			<button class="sb-control-action-button sb-btn sb-btn-grey sbi-fb-fs"
					@click.prevent.default="openFeedTypesPopupCustomizer()">
				<div v-html="svgIcons['edit']"></div>
				<span>{{genericText.editSources}}</span>
			</button>

		</div>
		<?php
	}

	/**
	 * Shoppable Feed Disabled Output Control
	 *
	 * @param string $controlEditingTypeModel Control Editing Type Model.
	 * @since 6.0
	 * @access public
	 */
	public function get_control_shoppable_disabled_output($controlEditingTypeModel)
	{
		?>
		<div class="sb-control-shoppbale-disabled-ctn sb-control-imginfo-ctn"
			 v-if="control.viewId == 'shoppabledisabled'">
			<div class="sb-control-imginfo-elem sbi-fb-fs">
				<div class="sb-control-imginfo-icon sbi-fb-fs" v-html="svgIcons['shoppableDisabled']"></div>
				<div class="sb-control-imginfo-text sbi-fb-fs" data-textalign="left"
					 :data-lef="shouldDisableProFeatures">
					<strong
						class="sb-bold sb-dark-text"
						v-html="customizeScreensText.shoppableFeedScreen.headingRenew"
						v-if="shouldDisableProFeatures && !sbiLicenseInactiveState"
					>
					</strong>
					<strong
						class="sb-bold sb-dark-text"
						v-html="customizeScreensText.shoppableFeedScreen.heading1"
						v-if="!shouldDisableProFeatures && licenseTierFeatures.includes('shoppable_feeds')"
					>
					</strong>
					<strong
						class="sb-bold sb-dark-text"
						v-html="customizeScreensText.shoppableFeedScreen.headingActivate"
						v-if="sbiLicenseInactiveState"
					>
					</strong>
					<strong
						class="sb-bold sb-dark-text"
						v-html="customizeScreensText.shoppableFeedScreen.heading3"
						v-if="!shouldDisableProFeatures && !licenseTierFeatures.includes('shoppable_feeds')"
					>
					</strong>
					<span v-html="customizeScreensText.shoppableFeedScreen.description1"
						  v-if="!shouldDisableProFeatures && licenseTierFeatures.includes('shoppable_feeds')"></span>
					<span v-html="customizeScreensText.shoppableFeedScreen.descriptionRenew"
						  v-if="shouldDisableProFeatures || !licenseTierFeatures.includes('shoppable_feeds')"></span>
				</div>
				<button class="sb-button-standard sbi-btn sb-btn-blue sbi-fb-fs"
						@click.prevent.default="viewsActive.extensionsPopupElement = 'shoppablefeed'"
						v-if="shouldDisableProFeatures || !licenseTierFeatures.includes('shoppable_feeds')">
					{{genericText.learnMore}}
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Shoppable Feed Enabled Output Control
	 *
	 * @param string $controlEditingTypeModel Control Editing Type Model.
	 * @since 6.0
	 * @access public
	 */
	public function get_control_shoppable_enabled_output($controlEditingTypeModel)
	{
		?>
		<div class="sb-control-shoppbale-enbaled-ctn sb-control-imginfo-ctn"
			 v-if="control.viewId == 'shoppableenabled'">
			<div class="sb-control-imginfo-elem sbi-fb-fs">
				<div class="sb-control-imginfo-icon sbi-fb-fs" v-html="svgIcons['shoppableEnabled']"></div>
				<div class="sb-control-imginfo-text sbi-fb-fs" data-textalign="center">
					<strong class="sb-bold sb-dark-text "
							v-html="customizeScreensText.shoppableFeedScreen.heading2"></strong>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Shoppable Feed Selected Post
	 *
	 * @param string $controlEditingTypeModel Control Editing Type Model.
	 * @since 6.0
	 * @access public
	 */
	public function get_control_shoppable_selected_post_output($controlEditingTypeModel)
	{
		?>
		<div class="sb-control-shoppbale-selectedpost-ctn"
			 v-if="control.viewId == 'shoppableselectedpost' && shoppableFeed.postId != null">
			<strong v-html="genericText.selectedPost"></strong>
			<div class="sb-control-selectedpost-info sbi-fb-fs">
				<img :src="shoppableFeed.postMedia" alt="Selected Shoppable">
				<span v-html="shoppableFeed.postCaption"></span>
			</div>
			<div class="sb-control-selectedpost-input sbi-fb-fs">
				<span class="sbi-fb-fs" v-html="genericText.productLink"></span>
				<input type="text" class="sb-control-input sbi-fb-fs" v-model="shoppableFeed.postShoppableUrl"
					   :placeholder="genericText.enterProductLink">
			</div>
			<div class="sb-control-selectedpost-btns sbi-fb-fs">
				<button class="sb-shoppable-selectedpost-btn sbi-btn-grey"
						@click.prevent.default="addPostShoppableFeed()">
					<div v-html="svgIcons['checkmark']"></div>
					<span v-html="genericText.add"></span>
				</button>
				<button class="sb-shoppable-selectedpost-btn sbi-btn-grey"
						@click.prevent.default="cancelPostShoppableFeed()">
					<span v-html="genericText.cancel"></span>
				</button>
			</div>

		</div>
		<?php
	}

	/**
	 * Moderation Mode Ouptut
	 *
	 * @param string $controlEditingTypeModel Control Editing Type Model.
	 * @since 6.0
	 * @access public
	 */
	public function get_control_moderation_mode_output($controlEditingTypeModel)
	{
		?>
		<div class="sb-control-moderationmode-ctn" v-if="control.viewId == 'moderationmode'">
			<button class="sb-control-moderationmode-btn sb-btn sb-btn-right-icon sb-btn-grey sbi-fb-fs"
					v-if="!viewsActive.moderationMode" @click.prevent.default="openModerationMode()">
				<div class="sb-btn-right-txt">
					<div v-html="svgIcons['eye1']"></div>
					<span>{{genericText.moderateFeed}}</span>
				</div>
				<div class="sb-btn-right-chevron"></div>
			</button>

			<div class="sb-control-moderationmode-elements sbi-fb-fs" v-if="viewsActive.moderationMode">

				<div class="sb-control-switcher-ctn"
					 :data-active="<?php echo $controlEditingTypeModel; ?>[control.switcher.id] === control.switcher.options.enabled"
					 @click.prevent.default="changeSwitcherSettingValue(control.switcher.id, control.switcher.options.enabled, control.switcher.options.disabled, control.switcher.ajaxAction ? control.switcher.ajaxAction : false)">
					<div class="sb-control-switcher sb-tr-2"></div>
					<div class="sb-control-label" v-if="control.switcher.label"
						 :data-title="control.switcher.labelStrong ? 'true' : false">{{control.switcher.label}}
					</div>
				</div>

				<div
					v-if="<?php echo $controlEditingTypeModel; ?>[control.switcher.id] == control.switcher.options.enabled">
					<div class="sb-control-moderatiomode-selement sbi-fb-fs sb-control-before-brd">
						<div class="sb-control-elem-label-title sbi-fb-fs">
							<div class="sb-control-elem-heading sb-small-p sb-dark-text">
								{{genericText.moderationMode}}
							</div>
						</div>
						<div class="sb-control-toggle-set-ctn sb-control-toggle-set-desc-ctn sbi-fb-fs">
							<div class="sb-control-toggle-elm sbi-fb-fs sb-tr-2"
								 v-for="(moderationItem, moderationId) in control.moderationTypes "
								 @click.prevent.default="switchModerationListType(moderationId)"
								 :data-active="moderationSettings.list_type_selected == moderationId">
								<div class="sb-control-toggle-deco sb-tr-1"></div>
								<div class="sb-control-content">
									<div class="sb-control-label">{{moderationItem.label}}</div>
									<div class="sb-control-toggle-description">{{moderationItem.description}}</div>
								</div>
							</div>
						</div>
					</div>

					<div class="sb-control-moderatiomode-selement sbi-fb-fs sb-control-before-brd">
						<div class="sb-control-elem-label-title sbi-fb-fs">
							<div class="sb-control-elem-heading sb-small-p sb-dark-text">
								{{genericText.moderationModeEnterPostId}}
							</div>
						</div>
						<div class="sbi-fb-fs">
							<textarea class="sb-control-input-textrea sbi-fb-fs" v-model="customBlockModerationlistTemp"
									  :placeholder="genericText.moderationModePostIdPlaceholder"></textarea>
						</div>
					</div>
					<!--
					<div class="sb-control-moderationmode-action-btns sb-control-before-brd sbi-fb-fs">
						<button class="sb-btn sb-btn-blue sbi-fb-fs" @click.prevent.default="saveModerationSettings()">
							<div class="sbi-fb-icon-success"></div>
							{{genericText.moderateFeedSaveExit}}
						</button>
						<button class="sb-btn sb-btn-grey sbi-fb-fs" @click.prevent.default="activateView('moderationMode'); moderationShoppableMode = false;">
							<div class="sbi-fb-icon-cancel"></div>
							{{genericText.cancel}}
						</button>
					</div>
					-->
				</div>


			</div>
		</div>
		<?php
	}

	/**
	 * Feed Templates Output Control
	 *
	 * @param string $controlEditingTypeModel Control Editing Type Model.
	 * @since 4.0
	 * @access public
	 */
	public function get_control_feed_template_output($controlEditingTypeModel)
	{
		?>
		<div
			:class="['sb-control-feedtype-ctn sb-control-feedtemplate-ctn', 'sbi-feedtemplate-' + customizerScreens.printedTemplate.type]"
			v-if="control.viewId == 'feedtemplate'">
			<div class="sbi-fb-type-el" v-if="customizerFeedTemplatePrint()"
				 @click.prevent.default="activateView('feedtemplatesPopup')">
				<div class="sbi-fb-type-el-img sbi-fb-fs"
					 v-html="svgIcons[customizerScreens.printedTemplate.icon]"></div>
				<div class="sbi-fb-type-el-info sbi-fb-fs">
					<strong class="sbi-fb-fs"
							v-html="getFeedTemplateElTitle(customizerScreens.printedTemplate, true)"></strong>
				</div>
			</div>
			<button class="sb-control-action-button sb-btn sbi-fb-fs sb-btn-grey"
					@click.prevent.default="activateView('feedtemplatesPopup')">
				<div v-html="svgIcons['edit']"></div>
				<span>{{genericText.change}}</span>
			</button>
		</div>
		<?php
	}

	/**
	 * Feed Theme Output Control
	 *
	 * @param string $controlEditingTypeModel Control Editing Type Model.
	 * @since 4.4
	 * @access public
	 */
	public function get_control_feedtheme_output($controlEditingTypeModel)
	{
		?>
		<div id="sb-control-feedtheme"
			 :class="['sb-control-feedtype-ctn sb-control-feedtheme-ctn', 'sbi-feedtheme-' + customizerScreens.printedTheme.type]"
			 v-if="control.viewId == 'feedtheme'">
			<div class="sbi-fb-type-el" v-if="customizerFeedThemePrint()"
				 @click.prevent.default="activateView('feedthemePopup')">
				<div class="sbi-fb-type-el-img sbi-fb-fs">
					<!-- default -->
					<div v-if="customizerFeedData.settings.feedtheme == 'default_theme'">
						<img
							src="<?php echo esc_url(SBI_PLUGIN_URL . 'admin/assets/img/feed-theme/default_theme.jpg'); ?>"
							width="100%" alt="default">
					</div>

					<!-- modern -->
					<div v-if="customizerFeedData.settings.feedtheme == 'modern'">
						<img src="<?php echo esc_url(SBI_PLUGIN_URL . 'admin/assets/img/feed-theme/modern.jpg'); ?>"
							 width="100%" alt="modern">
					</div>

					<!-- socila wall -->
					<div v-if="customizerFeedData.settings.feedtheme == 'social_wall'">
						<img
							src="<?php echo esc_url(SBI_PLUGIN_URL . 'admin/assets/img/feed-theme/social_wall.jpg'); ?>"
							width="100%" alt="social wall">
					</div>

					<!-- outline -->
					<div v-if="customizerFeedData.settings.feedtheme == 'outline'">
						<img src="<?php echo esc_url(SBI_PLUGIN_URL . 'admin/assets/img/feed-theme/outline.jpg'); ?>"
							 width="100%" alt="outline">
					</div>

					<!-- overlap -->
					<div v-if="customizerFeedData.settings.feedtheme == 'overlap'">
						<img src="<?php echo esc_url(SBI_PLUGIN_URL . 'admin/assets/img/feed-theme/overlap.jpg'); ?>"
							 width="100%" alt="overlap">
					</div>

				</div>
				<div class="sbi-fb-type-el-info sbi-fb-fs">
					<strong class="sbi-fb-fs" v-html="customizerScreens.printedTheme.title"></strong>
				</div>
			</div>
			<button class="sb-control-action-button sb-btn sbi-fb-fs sb-btn-grey"
					@click.prevent.default="activateView('feedthemePopup')">
				<div>
					<svg width="10" height="7" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M1.175 0.160156L5 3.97682L8.825 0.160156L10 1.33516L5 6.33516L0 1.33516L1.175 0.160156Z"
							fill="#141B38"/>
					</svg>
				</div>
				<span>{{genericText.change}}</span>
			</button>
			<ul class="sb-theme-options" v-if="viewsActive.feedThemeDropdown">
				<li
					class="sb-theme-option"
					v-for="{title, type} in feedThemes"
					@click.prevent.default="updateFeedThemeCustomizer(type)"
					@mouseover="themePreview(type)"
					@mouseleave="themePreviewClear()"
				>
					<span>{{title}}</span>
					<span class="sb-theme-active" v-if="title == customizerScreens.printedTheme.title">{{genericText.active}}</span>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Info Output
	 *
	 * @param string $controlEditingTypeModel Control Editing Type Model.
	 * @since 6.3
	 */
	public function get_control_likescomments_info_output($controlEditingTypeModel)
	{
		?>
		<div class="sb-customizer-sidebar-sec-elinfo sbi-fb-fs sb-control-likescommentsinfo-element"
			 v-if="control.viewId == 'likesCommentsInfo' && checkPersonalAccount()">
			<div class="sb-customizer-sidebar-sec-el-content">
				<div class="sb-small-p sb-bold sb-dark-text">{{genericText.likesCommentsInfo.heading}}</div>
				<div class="sb-small-p">{{genericText.likesCommentsInfo.info}}</div>
				<div class="sb-small-p sb-bold sb-linkText" v-html="genericText.likesCommentsInfo.linkText"></div>
			</div>
			<div class="sb-customizer-icon" v-html="svgIcons['likesCommentsSVG']"></div>
		</div>
		<?php
	}
}
