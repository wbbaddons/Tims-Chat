<div id="chatAttachmentUploadDialog" class="jsStaticDialogContent" data-title="{lang}wcf.attachment.attachments{/lang}">
	<div class="attachmentPreview"></div>

	{* placeholder for the upload button *}
	<div class="upload"></div>
	<small>{lang}wcf.attachment.upload.limits{/lang}</small>
</div>
<script data-relocate="true">
	require([ 'Language' ], function (Language) {
		Language.addObject({
			'wcf.attachment.upload.error.invalidExtension': '{lang}wcf.attachment.upload.error.invalidExtension{/lang}',
			'wcf.attachment.upload.error.tooLarge': '{lang}wcf.attachment.upload.error.tooLarge{/lang}',
			'wcf.attachment.upload.error.reachedLimit': '{lang}wcf.attachment.upload.error.reachedLimit{/lang}',
			'wcf.attachment.upload.error.reachedRemainingLimit': '{lang}wcf.attachment.upload.error.reachedRemainingLimit{/lang}',
			'wcf.attachment.upload.error.uploadFailed': '{lang}wcf.attachment.upload.error.uploadFailed{/lang}',
			'wcf.attachment.upload.error.uploadPhpLimit': '{lang}wcf.attachment.upload.error.uploadPhpLimit{/lang}',
		})
	})
</script>
