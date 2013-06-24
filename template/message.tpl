{literal}
	<div class="messageIcon">
		{if $message.type == $messageTypes.LEAVE || $message.type == $messageTypes.JOIN}
			<span class="icon icon16 icon-{if $message.type == $messageTypes.LEAVE}signout{else}signin{/if}"></span>
		{/if}
	</div>
	<div class="innerMessageContainer{if $message.type == $messageTypes.NORMAL || $message.type == $messageTypes.WHISPER || $message.type == $messageTypes.INFORMATION} bubble{/if}{if $message.type == $messageTypes.WHISPER && $message.sender != $__wcf.User.userID} right{/if}">
		<div class="userAvatar framed">
			{if $message.type != $messageTypes.INFORMATION}
				{if $message.type == $messageTypes.NORMAL || $message.type == $messageTypes.WHISPER}
					{@$message.avatar[32]}
				{else}
					{@$message.avatar[16]}
				{/if}
			{else}
				<span class="icon icon32 icon-info-sign"></span>
			{/if}
		</div>
		<div class="innerMessage">
			<span class="username">
			{if ($message.type == $messageTypes.WHISPER && $message.sender == WCF.User.userID) || $message.type != $messageTypes.WHISPER}
				{@$message.formattedUsername}
			{else}
				{$message.additionalData.receiverUsername}
			{/if}
			
			{if $message.type == $messageTypes.WHISPER}
				<span class="icon icon16 icon-double-angle-{if $message.sender == WCF.User.userID}right{else}left{/if} jsTooltip" title="{/literal}{lang}chat.ui.whispers{/lang}{literal}" onclick="be.bastelstu.Chat.insertText('/whisper {if $message.receiver == WCF.User.userID}{$message.username.replace("\\", "\\\\").replace("'", "\\'")}{else}{$message.additionalData.receiverUsername.replace("\\", "\\\\").replace("'", "\\'")}{/if}, ', { append: false });"></span>
				{if ($message.type == $messageTypes.WHISPER && $message.sender == WCF.User.userID) || $message.type != $messageTypes.WHISPER}
					{$message.additionalData.receiverUsername}
				{else}
					{@$message.formattedUsername}
				{/if}
			{/if}
			</span>
			
			<time>{@$message.formattedTime}</time>
			
			{if $message.type == $messageTypes.NORMAL || $message.type == $messageTypes.WHISPER}
				<ul class="text">
					<li>
						{if $message.isFollowUp} <time>{@$message.formattedTime}</time>{/if}
						{@$message.formattedMessage}
					</li>
				</ul>
			{else}
				<span class="text">{@$message.formattedMessage}</span>
			{/if}
		</div>
		<span class="markContainer">
			<input type="checkbox" value="{@$message.messageID}" />
		</span>
	</div>
{/literal}
