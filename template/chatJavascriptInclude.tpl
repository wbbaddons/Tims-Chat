<script type="text/javascript" src="{@$__wcf->getPath('wcf')}js/be.bastelstu.WCF.Chat.js{if $chatVersion|isset}?version={$chatVersion|urlencode}{/if}"></script>
<script type="text/javascript" src="{@$__wcf->getPath('wcf')}js/jCounter.jQuery.js"></script>
{if CHAT_SOCKET_IO_PATH}<script type="text/javascript" src="{CHAT_SOCKET_IO_PATH}/socket.io/socket.io.js"></script>{/if}
<script type="text/javascript" src="{@$__wcf->getPath('wcf')}js/be.bastelstu.WCF.Chat.Log.js"></script>
{event name='javascript'}