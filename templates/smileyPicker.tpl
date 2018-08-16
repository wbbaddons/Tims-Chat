{if MODULE_SMILEY && !$smileyCategories|empty}
	<div id="smileyPickerContainer" style="display: none;">
		{include file='messageFormSmilies'}
		<span id="smileyPickerCloseButton">{lang}wcf.global.button.close{/lang}</span>
	</div>
{/if}