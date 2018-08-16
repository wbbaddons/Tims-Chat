{literal}
<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-temproomCreated">
	<div class="chatMessageContainer inline">
		<div class="chatMessageSide">
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<div class="chatMessage">{lang}chat.messageType.{$message.objectType}{/lang}</div>
		</div>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-temproomInvited">
	<div class="chatMessageContainer inline">
		<div class="chatMessageSide">
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<div class="chatMessage">{lang}chat.messageType.{$message.objectType}{if $message.isOwnMessage()}.invitor{else}.invitee{/if}{/lang}</div>
		</div>
	</div>
</script>
{/literal}
