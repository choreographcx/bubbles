<div id="sb-footer-banner" class="sbi-fb-fs sbi-bld-footer"
	 :class="(viewsActive.pageScreen == 'welcome' && feedsList != null && feedsList.length != 0) ? 'sbi-fb-full-wrapper' : 'sbi-fb-wrapper'"
	 v-if="(!viewsActive.footerDiabledScreens.includes(viewsActive.pageScreen) || (viewsActive.pageScreen == 'welcome' && feedsList != null && feedsList.length != 0)) && !iscustomizerScreen">
	<div class="sb-box-shadow">
		<div class="sbi-bld-ft-content">
			<div class="sbi-bld-ft-img"><img :src="mainFooterScreen.image" alt=""></div>
			<div class="sbi-txt-btn-wrapper">
				<div class="sbi-bld-ft-txt">
					<h3 class="sbi-bld-ft-title" v-html="mainFooterScreen.heading"></h3>
					<div class="sbi-bld-ft-info sb-small-p" v-html="mainFooterScreen.description"></div>
				</div>
				<div class="sbi-bld-ft-action">
					<a :href="mainFooterScreen.link ? mainFooterScreen.link : links.allAccessBundle" target="_blank"
					   class="sb-button sbi-btn-green">{{mainFooterScreen.learnMore}}
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M3.3335 11.7266L10.3935 4.66659H6.00016V3.33325H12.6668V9.99992H11.3335V5.60659L4.2735 12.6666L3.3335 11.7266Z"
								fill="white"/>
						</svg>
					</a>
				</div>
			</div>
		</div>
		<div v-if="pluginType !== 'pro' && mainFooterScreen.promo !== ''" class="sbi-bld-ft-btm"
			 v-html="mainFooterScreen.promo"></div>
	</div>
</div>
<?php
include_once SBI_BUILDER_DIR . 'templates/sections/popup/add-source-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/license-learn-more.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/why-renew-license-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/personal-account-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/sources-list-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/extensions-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/feedtemplates-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/feedtypes-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/feedtypes-customizer-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/confirm-dialog-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/embed-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/onboarding-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/onboarding-customizer-popup.php';
include_once SBI_BUILDER_DIR . 'templates/sections/popup/install-plugin-popup.php';
?>
<div class="sb-notification-ctn" :data-active="notificationElement.shown" :data-type="notificationElement.type">
	<div class="sb-notification-icon" v-html="svgIcons[notificationElement.type+'Notification']"></div>
	<span class="sb-notification-text" v-html="notificationElement.text"></span>
</div>

<div class="sb-full-screen-loader" :data-show="fullScreenLoader ? 'shown' :  'hidden'">
	<div class="sb-full-screen-loader-logo">
		<div class="sb-full-screen-loader-spinner"></div>
		<div class="sb-full-screen-loader-img" v-html="svgIcons['smash']"></div>
	</div>
	<div class="sb-full-screen-loader-txt">
		Loading...
	</div>
</div>

<sb-personal-account-component
	:generic-text="genericText"
	:svg-icons="svgIcons"
	ref="personalAccountRef">
</sb-personal-account-component>


<sb-confirm-dialog-component
	:dialog-box.sync="dialogBox"
	:source-to-delete="sourceToDelete"
	:svg-icons="svgIcons"
	:parent-type="'builder'"
	:generic-text="genericText"></sb-confirm-dialog-component>

<sb-add-source-component
	:sources-list="sourcesList"
	:select-source-screen="selectSourceScreen"
	:views-active="viewsActive"
	:generic-text="genericText"
	:selected-feed="selectedFeed"
	:svg-icons="svgIcons"
	:links="links"
	ref="addSourceRef">
</sb-add-source-component>

<install-plugin-popup
	:views-active="viewsActive"
	:generic-text="genericText"
	:svg-icons="svgIcons"
	:plugins="plugins[viewsActive.installPluginModal]">
</install-plugin-popup>
