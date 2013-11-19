{literal}
	{if $message.type == $messageTypes.LEAVE || $message.type == $messageTypes.JOIN}
		<div class="messageIcon">
			<span class="icon icon16 icon-{if $message.type == $messageTypes.LEAVE}signout{elseif $message.type == $messageTypes.JOIN}signin{/if}"></span>
		</div>
	{/if}
	<div class="innerMessageContainer{if $message.type == $messageTypes.NORMAL || $message.type == $messageTypes.WHISPER || $message.type == $messageTypes.INFORMATION || $message.type == $messageTypes.ATTACHMENT} bubble{/if}{if $message.type == $messageTypes.WHISPER && $message.sender != $__wcf.User.userID} right{/if}">
		<div class="avatarContainer">
			<div class="userAvatar{if $message.type != $messageTypes.INFORMATION} framed{/if}">
				{if $message.type != $messageTypes.INFORMATION}
					{if $message.type == $messageTypes.NORMAL || $message.type == $messageTypes.WHISPER || $message.type == $messageTypes.ATTACHMENT}
						{@$message.avatar[32]}
					{else}
						{@$message.avatar[16]}
					{/if}
				{else}
					<span class="icon icon32 icon-info-sign"></span>
				{/if}
			</div>
			{if $message.type == $messageTypes.ATTACHMENT}
				<small class="framed avatarExtra">
					<span class="icon icon16 icon-paperclip"></span>
				</small>
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
			
			{if $message.type == $messageTypes.NORMAL || $message.type == $messageTypes.WHISPER || $message.type == $messageTypes.ATTACHMENT}
				{if $message.type == $messageTypes.ATTACHMENT}<span>{lang}chat.message.{$messageTypes.ATTACHMENT}{/lang}</span>{/if}
				<ul class="text">
					<li>
						{if $message.isFollowUp} <time>{@$message.formattedTime}</time>{/if}
						{@$message.formattedMessage}
					</li>
				</ul>
			{elseif $message.type == $messageTypes.INFORMATION}
				<div class="text">{@$message.formattedMessage}</div>
			{else}
				<span class="text">{@$message.formattedMessage}</span>
			{/if}
		</div>
		<span class="markContainer">
			<input type="checkbox" value="{@$message.messageID}" />
		</span>
	</div>
{/literal}
