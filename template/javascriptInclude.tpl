{if !'LAST_UPDATE_TIME'|defined}
	{if MODULE_ATTACHMENT && $__wcf->session->getPermission('user.chat.canUploadAttachment')}<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Attachment{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>{/if}
{/if}
<script data-relocate="true" src="{$__wcf->getPath('chat')}js/be.bastelstu.Chat{if !ENABLE_DEBUG_MODE}.min{/if}.js?version={PACKAGE_VERSION|rawurlencode}"></script>
{event name='javascript'}
