<div id="chatAttachmentUploadDialog" class="jsStaticDialogContent" data-title="{lang}wcf.attachment.attachments{/lang}">
	<div class="attachmentPreview"></div>

	{* placeholder for the upload button *}
	<div class="upload"></div>
	<small>{lang}wcf.attachment.upload.limits{/lang}</small>
</div>
<script data-relocate="true">
	require([ 'Language' ], function (Language) {
		Language.addObject({
			'wcf.attachment.upload.error.invalidExtension': '{jslang}wcf.attachment.upload.error.invalidExtension{/jslang}',
			'wcf.attachment.upload.error.tooLarge': '{jslang}wcf.attachment.upload.error.tooLarge{/jslang}',
			'wcf.attachment.upload.error.reachedLimit': '{jslang}wcf.attachment.upload.error.reachedLimit{/jslang}',
			'wcf.attachment.upload.error.reachedRemainingLimit': '{jslang}wcf.attachment.upload.error.reachedRemainingLimit{/jslang}',
			'wcf.attachment.upload.error.uploadFailed': '{jslang}wcf.attachment.upload.error.uploadFailed{/jslang}',
			'wcf.attachment.upload.error.uploadPhpLimit': '{jslang}wcf.attachment.upload.error.uploadPhpLimit{/jslang}',
		})
	})
</script>
