<div class="sb-customizer-ctn sbi-fb-fs" v-if="iscustomizerScreen">
	<?php include_once SBI_BUILDER_DIR . 'templates/sections/customizer/sidebar.php'; ?>

	<div class="sbi-customizer-theme-preview">
		<div class="sbi-theme-preview-item" :class="previewTheme ? 'sbi-theme-preview-show' : ''">
			<img v-if="previewTheme"
				 :src="pluginURL + 'admin/assets/img/feed-theme/preview-images/' + previewTheme + '.jpg'"
				 alt="previewTheme">
		</div>
	</div>

	<?php include_once SBI_BUILDER_DIR . 'templates/sections/customizer/preview.php'; ?>
</div>
<div v-html="feedStyleOutput != false ? feedStyleOutput : ''"></div>
<script type="text/x-template" id="sbi-colorpicker-component">
	<input type="text" v-bind:value="color" placeholder="Select">
</script>
