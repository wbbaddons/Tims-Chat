{literal}
	<div class="innerMessageContainer{if $type == 0 || $type == 7} normal{/if}">
		<div class="userAvatar framed">
			{if $type == 0 || $type == 7}
				{@$avatar[32]}
			{else}
				{@$avatar[16]}
			{/if}
		</div>
		<div class="innerMessage">
			<span class="username">
			{@$formattedUsername}
			{if $type == 7}
				<span class="icon icon16 icon-double-angle-right jsTooltip" title="{/literal}{lang}chat.ui.whispers{/lang}{literal}" onclick="be.bastelstu.Chat.insertText('/whisper {if $receiver == WCF.User.userID}{$username.replace("\\", "\\\\").replace("'", "\\'")}{else}{$additionalData.receiverUsername.replace("\\", "\\\\").replace("'", "\\'")}{/if}, ', { append: false });"></span>
				{$additionalData.receiverUsername}
			{/if}
			</span>
			<time>{@$formattedTime}</time>
			{if $type == 0 || $type == 7}
				<ul>
					<li class="text">{@$formattedMessage}</li>
				</ul>
			{else}
				<span class="text">{@$formattedMessage}</span>
			{/if}
		</div>
		<span class="markContainer">
			<input type="checkbox" value="{@$messageID}" />
		</span>
	</div>
{/literal}
