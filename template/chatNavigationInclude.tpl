{if $templateName == 'chat'}
<li><a href="{link controller="Chat" action="Log"}{/link}" title="{lang}wcf.chat.protocol{/lang}" class="wcf-balloonTooltip"><img src="{icon size='S'}session1{/icon}" alt="" /> <span>{lang}wcf.chat.protocol{/lang}</span></a></li>
<li><a href="{link controller="Chat"}{/link}"              title="{lang}wcf.chat.title{/lang}"    class="wcf-balloonTooltip"><img src="{icon size='S'}chat{/icon}" alt="" /> <span>{lang}wcf.chat.title{/lang}</span></a></li>
{/if}