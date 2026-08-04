<div class="sbi-fb-types-ctn sbi-fb-templates-ctn sbi-fb-fs sb-box-shadow"
	 v-if="viewsActive.selectedFeedSection == 'selectTheme'">
	<div class="sbi-fb-types sbi-fb-fs">
		<h4>{{selectFeedThemeScreen.feedThemeHeading}}</h4>
		<p class="sb-caption sb-lighter">{{selectFeedThemeScreen.feedThemeDescription}}</p>
		<div class="sbi-fb-templates-list sbi-feed-theme-list">
			<div class="sbi-fb-type-el" v-for="(feedTemplateEl, feedTemplateIn) in feedThemes"
				 :data-active="selectedFeedTheme === feedTemplateEl.type"
				 @click.prevent.default="chooseFeedTheme(feedTemplateEl)">
				<div :class="['sbi-fb-type-el-img sbi-fb-fs', 'sbi-feedtemplate-' + feedTemplateEl.type]">
					<img
						:src="pluginURL + 'admin/assets/img/feed-theme/' + feedTemplateEl.type + '.jpg'"
						alt="feedTemplateEl.type">
				</div>
				<div class="sbi-fb-type-el-info sbi-fb-fs">
					<p class="sb-small-p sb-bold sb-dark-text" v-html="getFeedTemplateElTitle(feedTemplateEl)"></p>
				</div>
			</div>
		</div>
	</div>
</div>
