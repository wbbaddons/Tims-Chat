{literal}
	<time>{@$formattedTime}</time>
	<span class="usernameContainer">
		<span class="username">{*
			*}{if $type != 7}{*
				*}{@$formattedUsername}{*
			*}{else}
				{if $receiver == WCF.User.userID}
					{@$formattedUsername}
				{/if}
				<span class="icon icon16 icon-double-angle-right jsTooltip" title="{/literal}{lang}chat.ui.whispers{/lang}{literal}" onclick="be.bastelstu.Chat.insertText('/whisper {if $receiver == WCF.User.userID}{$username.replace("\\", "\\\\").replace("'", "\\'")}{else}{$additionalData.receiverUsername.replace("\\", "\\\\").replace("'", "\\'")}{/if}, ', { append: false });"></span>
				{if $receiver != WCF.User.userID}
					{$additionalData.receiverUsername}{/if}{*
				*}{/if}{*
		*}</span>{*
		*}{if $receiver != WCF.User.userID}{*
			*}<span class="separator">{$separator}</span>
		{/if}
	</span>
	<span class="text">{@$formattedMessage}</span>
	<span class="markContainer">
		<input type="checkbox" value="{@$messageID}" />
	</span>
{/literal}
