<script type="text/javascript" src="{@$__wcf->getPath('wcf')}js/be.bastelstu.WCF.Chat.js{if $chatVersion|isset}?version={$chatVersion|urlencode}{/if}"></script>
<script type="text/javascript" src="{@$__wcf->getPath('wcf')}js/jCounter.jQuery.js"></script>
{if CHAT_SOCKET_IO_PATH}<script type="text/javascript" src="{CHAT_SOCKET_IO_PATH}/socket.io/socket.io.js"></script>{/if}
{if $templateName == 'chatLog'}
	<script type="text/javascript" src="{@$__wcf->getPath('wcf')}js/be.bastelstu.WCF.Chat.Log.js{if $chatVersion|isset}?version={$chatVersion|urlencode}{/if}"></script>
{/if}
{event name='javascript'}