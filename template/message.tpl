{literal}
	{if $message.type == $messageTypes.JOIN || $message.type == $messageTypes.LEAVE}
		<div class="timsChatMessageIcon">
			<span class="icon icon16 icon-{if $message.type == $messageTypes.JOIN}signin{else}signout{/if}"></span>
		</div>
	{/if}
	<div class="timsChatInnerMessageContainer{if $message.type == $messageTypes.NORMAL || $message.type == $messageTypes.WHISPER || $message.type == $messageTypes.INFORMATION || $message.type == $messageTypes.ATTACHMENT} bubble{/if}{if $message.type == $messageTypes.WHISPER && $message.sender != $__wcf.User.userID} right{/if}">
		<div class="timsChatAvatarContainer">
			<div class="userAvatar framed">
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
				<small class="framed timsChatAvatarExtraIcon">
					<span class="icon icon16 icon-paperclip"></span>
				</small>
			{/if}
		</div>
		<div class="timsChatInnerMessage">
			<span class="timsChatUsernameContainer">
				{@$message.formattedUsername}
			
				{if $message.type == $messageTypes.WHISPER}
					<span class="icon icon16 icon-double-angle-right jsTooltip pointer" title="{/literal}{lang}chat.global.whispers{/lang}{literal}" onclick="be.bastelstu.Chat.insertText('/whisper {if $message.receiver == WCF.User.userID}{$message.username.replace("\\", "\\\\").replace("'", "\\'")}{else}{$message.additionalData.receiverUsername.replace("\\", "\\\\").replace("'", "\\'")}{/if}, ', { append: false });"></span>
					<span class="reciever">{$message.additionalData.receiverUsername}</span>
				{/if}
			</span>
			
			<time>{@$message.formattedTime}</time>
			
			{if $message.type == $messageTypes.NORMAL || $message.type == $messageTypes.WHISPER || $message.type == $messageTypes.ATTACHMENT}
				{if $message.type == $messageTypes.ATTACHMENT}<span>{lang}chat.message.{$messageTypes.ATTACHMENT}{/lang}</span>{/if}
				<ul class="timsChatText">
					<li>
						{if $message.isFollowUp} <time>{@$message.formattedTime}</time>{/if}
						{@$message.formattedMessage}
					</li>
				</ul>
			{elseif $message.type == $messageTypes.INFORMATION}
				<div class="timsChatText">{@$message.formattedMessage}</div>
			{else}
				<span class="timsChatText">{@$message.formattedMessage}</span>
			{/if}
		</div>
		<span class="timsChatMarkContainer">
			<input type="checkbox" value="{@$message.messageID}" />
		</span>
	</div>
{/literal}
