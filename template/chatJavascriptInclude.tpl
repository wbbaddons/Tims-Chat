{if $templateName == 'chat'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TimWolla.WCF.Chat.js{if $chatVersion|isset}?version={$chatVersion|urlencode}{/if}"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/jCounter.jQuery.js"></script>
{/if}