<div v-if="selected === 'app-4'">
	<div class="sb-tab-box sb-wpcode-snippets clearfix"
		 v-if="(!sbi_settings.wpCode.pluginInstalled || !sbi_settings.wpCode.pluginActive)">
		<div class='sb-wpcode-install-snippets'>
			<div class='sb-wpcode-install-snippets-content'>
				<svg width="33" height="44" viewBox="0 0 33 44" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
						d="M29.5817 38.3853H3.3745C1.49004 38.3853 0 36.8984 0 35.018V8.86724C0 6.98683 1.49004 5.5 3.3745 5.5H29.5817C31.4661 5.5 32.9562 6.98683 32.9562 8.86724V35.018C32.9562 36.8984 31.4661 38.3853 29.5817 38.3853Z"
						fill="#0068A0"/>
					<path
						d="M8.1519 32.2632C7.9766 32.2632 7.75748 32.2194 7.58218 32.1757C6.79334 31.8696 6.39892 30.995 6.70569 30.2078L13.5862 12.5845C13.8929 11.7973 14.7694 11.4037 15.5583 11.7099C16.3471 12.016 16.7415 12.8906 16.4348 13.6777L9.59812 31.3011C9.37899 31.9133 8.76545 32.2632 8.1519 32.2632Z"
						fill="white"/>
					<path
						d="M19.1105 30.47C18.716 30.47 18.3216 30.3388 18.0148 30.0327C17.4013 29.4204 17.4013 28.4584 18.0148 27.8899L23.0109 22.9046L18.0587 17.9193C17.4451 17.3071 17.4451 16.345 18.0587 15.7766C18.6722 15.1643 19.6364 15.1643 20.2061 15.7766L25.5965 21.1554C26.5607 22.1175 26.5607 23.7355 25.5965 24.6976L20.2061 30.0327C19.8993 30.3388 19.5049 30.47 19.1105 30.47Z"
						fill="white"/>
				</svg>

				<div class='sb-wpcode-install-snippets-header'>
					<p class='sb-wpcode-install-snippets-header-title'>
						{{sbi_settings.wpCode.pluginInstalled ? sbi_settings.codeSnippetsTab.activateTitle :
						sbi_settings.codeSnippetsTab.installTitle}}
					</p>

					<p class='sb-wpcode-install-snippets-header-description'>
						{{sbi_settings.codeSnippetsTab.description}}
					</p>
				</div>
			</div>

			<div class='sb-wpcode-install-cta-section'>
				<button class='sb-btn sb-btn-primary' @click.prevent.default="installActivateWPCodePlugin()">
					<svg width="17" height="16" viewBox="0 0 17 16" fill="none"
						 v-if="(!loading && !sbi_settings.wpCode.pluginInstalled && !sbi_settings.wpCode.pluginActive)">
						<path
							d="M12.5 9.99996V12H4.49996V9.99996H3.16663V12C3.16663 12.7333 3.76663 13.3333 4.49996 13.3333H12.5C13.2333 13.3333 13.8333 12.7333 13.8333 12V9.99996H12.5ZM11.8333 7.33329L10.8933 6.39329L9.16663 8.11329V2.66663H7.83329V8.11329L6.10663 6.39329L5.16663 7.33329L8.49996 10.6666L11.8333 7.33329Z"
							fill="white"/>
					</svg>
					<span v-if="loading && pressedBtnName === 'sbi'" v-html="loaderSVG"></span>
					{{sbi_settings.wpCode.pluginInstalled ? sbi_settings.codeSnippetsTab.activateButton :
					sbi_settings.codeSnippetsTab.installButton}}
				</button>

				<button class='sb-btn sb-btn-secondary' @click.prevent.default="goToWPCodeDocumentation()">
					{{sbi_settings.codeSnippetsTab.learnMore}}
					<svg width="6" height="8" viewBox="0 0 6 8" fill="#141B38" xmlns="http://www.w3.org/2000/svg">
						<path d="M1.66681 0L0.726807 0.94L3.78014 4L0.726807 7.06L1.66681 8L5.66681 4L1.66681 0Z"/>
					</svg>
				</button>
			</div>
		</div>
	</div>

	<div class="sb-tab-box snippet-card-grid clearfix"
		 v-if="(sbi_settings.wpCode.pluginInstalled && sbi_settings.wpCode.pluginActive && sbi_settings.wpCode.snippets.length != 0)">
		<div class="snippet-card" v-for="(snippet, index) in sbi_settings.wpCode.snippets" :key="index">
			<div class='snippet-card-icon'>
				<svg width="18" height="20" viewBox="0 0 18 20" fill="none">
					<path
						d="M8 17.425V10.575L2 7.1V13.95L8 17.425ZM10 17.425L16 13.95V7.1L10 10.575V17.425ZM9 8.85L14.925 5.425L9 2L3.075 5.425L9 8.85ZM1 15.7C0.683333 15.5167 0.4375 15.275 0.2625 14.975C0.0875 14.675 0 14.3417 0 13.975V6.025C0 5.65833 0.0875 5.325 0.2625 5.025C0.4375 4.725 0.683333 4.48333 1 4.3L8 0.275C8.31667 0.0916667 8.65 0 9 0C9.35 0 9.68333 0.0916667 10 0.275L17 4.3C17.3167 4.48333 17.5625 4.725 17.7375 5.025C17.9125 5.325 18 5.65833 18 6.025V13.975C18 14.3417 17.9125 14.675 17.7375 14.975C17.5625 15.275 17.3167 15.5167 17 15.7L10 19.725C9.68333 19.9083 9.35 20 9 20C8.65 20 8.31667 19.9083 8 19.725L1 15.7Z"
						fill="#9295A6"/>
				</svg>
			</div>

			<div class='snippet-card-content'>
				<div>
					<div class='snippet-card-title'>{{snippet.title}}</div>
					<div class='snippet-card-description'>{{snippet.note}}</div>
				</div>

				<button class='sb-btn sb-btn-secondary' @click.prevent.default="editWPCodeSnippet(index)">
					<svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M9.66668 5.66668H5.66668V9.66668H4.33334V5.66668H0.333344V4.33334H4.33334V0.333344H5.66668V4.33334H9.66668V5.66668Z"
							fill="#222"/>
					</svg>
					{{snippet.installed ? sbi_settings.codeSnippetsTab.editSnippet :
					sbi_settings.codeSnippetsTab.useSnippet}}
				</button>
			</div>
		</div>
	</div>
</div>
