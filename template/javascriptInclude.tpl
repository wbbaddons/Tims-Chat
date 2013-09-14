{if MODULE_ATTACHMENT}<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Attachment{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>{/if}
<script data-relocate="true" src="{$__wcf->getPath('chat')}js/be.bastelstu.Chat{if !ENABLE_DEBUG_MODE}.min{/if}.js?version={PACKAGE_VERSION|rawurlencode}"></script>
<!--script src="{$__wcf->getPath('chat')}js/be.bastelstu.Chat.Log{if !ENABLE_DEBUG_MODE}.min{/if}.js?version={PACKAGE_VERSION|rawurlencode}"></script-->
{event name='javascript'}
