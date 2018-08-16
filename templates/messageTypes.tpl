<script>
	require([ 'Language' ], function (Language) {
		Language.addObject({
			'chat.messageType.be.bastelstu.chat.messageType.away': '{lang __literal=true}chat.messageType.be.bastelstu.chat.messageType.away{/lang}',
			'chat.messageType.be.bastelstu.chat.messageType.back': '{lang __literal=true}chat.messageType.be.bastelstu.chat.messageType.back{/lang}',
			'chat.messageType.be.bastelstu.chat.messageType.color': '{lang __literal=true}chat.messageType.be.bastelstu.chat.messageType.color{/lang}',
			'chat.messageType.be.bastelstu.chat.messageType.join': '{lang __literal=true}chat.messageType.be.bastelstu.chat.messageType.join{/lang}',
			'chat.messageType.be.bastelstu.chat.messageType.join.plain': '{lang __literal=true}chat.messageType.be.bastelstu.chat.messageType.join.plain{/lang}',
			'chat.messageType.be.bastelstu.chat.messageType.leave': '{lang __literal=true}chat.messageType.be.bastelstu.chat.messageType.leave{/lang}',
			'chat.messageType.be.bastelstu.chat.messageType.leave.plain': '{lang __literal=true}chat.messageType.be.bastelstu.chat.messageType.leave.plain{/lang}',
			'chat.messageType.be.bastelstu.chat.messageType.tombstone.message': '{lang __literal=true}chat.messageType.be.bastelstu.chat.messageType.tombstone.message{/lang}',
			'chat.suspension.message.new.be.bastelstu.chat.suspension.ban': '{lang __literal=true}chat.suspension.message.new.be.bastelstu.chat.suspension.ban{/lang}',
			'chat.suspension.message.new.be.bastelstu.chat.suspension.mute': '{lang __literal=true}chat.suspension.message.new.be.bastelstu.chat.suspension.mute{/lang}',
			'chat.suspension.message.revoke.be.bastelstu.chat.suspension.ban': '{lang __literal=true}chat.suspension.message.revoke.be.bastelstu.chat.suspension.ban{/lang}',
			'chat.suspension.message.revoke.be.bastelstu.chat.suspension.mute': '{lang __literal=true}chat.suspension.message.revoke.be.bastelstu.chat.suspension.mute{/lang}',
		})

		{event name='language'}
	})
</script>

{literal}
<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="DeleteButton">
	{/literal}
	<li>
		<a class="button jsTooltip jsDeleteButton" href="#" data-tooltip="{lang}wcf.global.button.delete{/lang}" data-confirm-message-html="{lang __encode=true __literal=true}chat.stream.button.delete.sure{/lang}"><span class="icon icon16 fa-times"></span> <span class="invisible">{lang}wcf.global.button.delete{/lang}</span></a>
	</li>
	{literal}
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-where">
	<div class="chatMessageContainer inline">
		<div class="chatMessageSide">
			<div class="chatUserAvatar">
				<span class="icon icon32 fa-info-circle"></span>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">{lang}chat.messageType.information{/lang}</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<div class="chatMessage">
				<ol class="containerList">
					{foreach from=$message.payload item='room'}
						{if $room.users.length}
							<li class="jsRoomInfo">
								<div class="containerHeadline">
									<h3><a href="{$room.room.link}">{$room.room.title}</a></h3>
								</div>

								<div class="containerContent">
									<ul class="inlineList commaSeparated">
										{foreach from=$room.users item='user'}
											<li><a href="{$user.link}" class="userLink" data-user-id="{$user.userID}">{$user.username}</a></li>
										{/foreach}
									</ul>
								</div>
							</li>
						{/if}
					{/foreach}
				</ol>
			</div>
		</div>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-info">
	<div class="chatMessageContainer inline">
		<div class="chatMessageSide">
			<div class="chatUserAvatar">
				<span class="icon icon32 fa-info-circle"></span>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">{lang}chat.messageType.information{/lang}</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<div class="chatMessage">
				<ol class="containerList">
					<li>
						<div class="box48">
							<a href="{$users.get($message.payload.user.userID).link}" title="{@$users.get($message.payload.user.userID).username}">
								{@$users.get($message.payload.user.userID).image48}
							</a>
							<div>
								<div class="containerHeadline">
									<h3>
										<a href="{$users.get($message.payload.user.userID).link}" class="username userLink" data-user-id="{$message.payload.user.userID}">{$users.get($message.payload.user.userID).username}</a>
										{if $users.get($message.payload.user.userID).userTitle}
											<span class="userTitle">
												<span class="badge userTitleBadge{if $users.get($message.payload.user.userID).userRankClass} {@$users.get($message.payload.user.userID).userRankClass}{/if}">	{$users.get($message.payload.user.userID).userTitle}</span>
											</span>
										{/if}
									</h3>
									{if $message.payload.away != null}
										<small>{lang}chat.messageType.be.bastelstu.chat.messageType.away.title{/lang}{if $message.payload.away !== ""}: {$message.payload.away}{/if}</small>
									{/if}
								</div>
							</div>
						</div>
					</li>

					{if $message.payload.rooms.length > 0}
						<li>
							<div class="containerHeadline">
								<h3>{lang}chat.messageType.be.bastelstu.chat.messageType.info.lastActivity{/lang}</h3>
							</div>
							<div class="containerContent">
								<div class="messageTableOverflow">
									<table class="table">
										<thead>
											<tr>
												<th>{lang}chat.messageType.be.bastelstu.chat.messageType.where.room{/lang}</th>
												<th>{lang}chat.messageType.be.bastelstu.chat.messageType.where.lastAction{/lang}</th>
												<th>{lang}chat.messageType.be.bastelstu.chat.messageType.where.lastFetch{/lang}</th>
											</tr>
										</thead>
										<tbody>
											{foreach from=$message.payload.rooms item='room'}
											<tr>
												<td><a href="{$room.link}">{$room.title}</a></td>
												<td>{if $room.lastPushHTML !== null}{@$room.lastPushHTML}{else}—{/if}</td>
												<td>{if $room.lastPullHTML !== null}{@$room.lastPullHTML}{else}—{/if}</td>
											</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
							</div>
						</li>
					{/if}
					{/literal}{event name='infoCommandContents'}{literal}
				</ol>
			</div>
		</div>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-away">
	<div class="chatMessageContainer inline">
		<div class="chatMessageSide">
			<div class="chatUserAvatar jsUserActionDropdown" data-user-id="{$author.userID}">
				<a href="{$author.link}">{@$author.image24}</a>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">
					<a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">
						{@$author.coloredUsername}
					</a>
				</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<span class="chatMessageIcon">
				<span class="icon icon16 {if $__window.RegExp('^shower(?:ing)?$', 'i').test($message.payload.message)}fa-shower{else if $__window.RegExp('^bath(?:ing)?$', 'i').test($message.payload.message)}fa-bath{else}fa-user-o{/if}"></span>
			</span>
			<div class="chatMessage">{lang}chat.messageType.{$message.objectType}{/lang}</div>
		</div>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-back">
	<div class="chatMessageContainer inline">
		<div class="chatMessageSide">
			<div class="chatUserAvatar jsUserActionDropdown" data-user-id="{$author.userID}">
				<a href="{$author.link}">{@$author.image24}</a>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">
					<a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">
						{@$author.coloredUsername}
					</a>
				</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<span class="chatMessageIcon">
				<span class="icon icon16 fa-user"></span>
			</span>
			<div class="chatMessage">{lang}chat.messageType.{$message.objectType}{/lang}</div>
		</div>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-me" data-template-includes="DeleteButton">
	<div class="chatMessageContainer inline">
		<div class="chatMessageSide">
			<div class="chatUserAvatar jsUserActionDropdown" data-user-id="{$author.userID}">
				<a href="{$author.link}">{@$author.image24}</a>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">
					<a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">
						{@$author.coloredUsername}
					</a>
				</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<div class="chatMessage"><a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">{@$author.coloredUsername}</a> {$message.payload.message}</div>
		</div>

		<ul class="buttonGroup buttonList smallButtons">
			{/literal}
			{if $__wcf->session->getPermission('mod.chat.canDelete')}
				{ldelim}include file=$t.DeleteButton}
			{/if}
			{literal}
		</ul>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-join">
	<div class="chatMessageContainer inline">
		<div class="chatMessageSide">
			<div class="chatUserAvatar jsUserActionDropdown" data-user-id="{$author.userID}">
				<a href="{$author.link}">{@$author.image24}</a>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">
					<a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">
						{@$author.coloredUsername}
					</a>
				</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<span class="chatMessageIcon">
				<span class="icon icon16 fa-sign-{if $message.objectType === 'be.bastelstu.chat.messageType.join'}in{else}out{/if}"></span>
			</span>
			<div class="chatMessage">{lang}chat.messageType.{$message.objectType}{/lang}</div>
		</div>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-leave" data-template-includes="be-bastelstu-chat-messageType-join">{include file=$t['be-bastelstu-chat-messageType-join']}</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-suspend">
	<div class="chatMessageContainer inline">
		<div class="chatMessageSide">
			<div class="chatUserAvatar jsUserActionDropdown" data-user-id="{$author.userID}">
				<a href="{$author.link}">{@$author.image24}</a>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">
					<a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">
						{@$author.coloredUsername}
					</a>
				</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<span class="chatMessageIcon">
				<span class="icon icon16 fa-user-times"></span>
			</span>
			<div class="chatMessage">{lang}chat.suspension.message.new.{$message.payload.suspension.objectType}{/lang}</div>
		</div>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-unsuspend">
	<div class="chatMessageContainer inline">
		<div class="chatMessageSide">
			<div class="chatUserAvatar jsUserActionDropdown" data-user-id="{$author.userID}">
				<a href="{$author.link}">{@$author.image24}</a>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">
					<a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">
						{@$author.coloredUsername}
					</a>
				</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<span class="chatMessageIcon">
				<span class="icon icon16 fa-user-times"></span>
			</span>
			<div class="chatMessage">{lang}chat.suspension.message.revoke.{$message.payload.objectType}{/lang}</div>
		</div>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-whisper">
	<div class="chatMessageContainer">
		<div class="chatMessageSide">
			<div class="chatUserAvatar jsUserActionDropdown" data-user-id="{$author.userID}">
				<a href="{$author.link}">{@$author.image32}</a>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">
					<a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">
						{@$author.coloredUsername}
					</a>
				</span>
				<span class="icon icon16 fa-chevron-right" data-insert-whisper="{if $message.isOwnMessage()}{$message.payload.recipientName}{else}{$author.username}{/if}"></span>
				<span class="recipientUsername">{$message.payload.recipientName}</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<div class="chatMessage htmlContent">{@$message.payload.formattedMessage}</div>
		</div>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-broadcast" data-template-includes="DeleteButton">
	<div class="chatMessageContainer">
		<div class="chatMessageSide">
			<div class="chatUserAvatar jsUserActionDropdown" data-user-id="{$author.userID}">
				<a href="{$author.link}">{@$author.image32}</a>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username"><span class="icon icon16 fa-bullhorn jsTooltip" title="{lang}chat.messageType.be.bastelstu.chat.messageType.broadcast.tooltip{/lang}"></span>
					<a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">
						{@$author.coloredUsername}
					</a>
				</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<div class="chatMessage htmlContent">{@$message.payload.formattedMessage}</div>
		</div>
		<ul class="buttonGroup buttonList smallButtons">
			{/literal}
			{if $__wcf->session->getPermission('mod.chat.canDelete')}
				{ldelim}include file=$t.DeleteButton}
			{/if}
			{literal}
		</ul>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-team" data-template-includes="DeleteButton">
	<div class="chatMessageContainer">
		<div class="chatMessageSide">
			<div class="chatUserAvatar jsUserActionDropdown" data-user-id="{$author.userID}">
				<a href="{$author.link}">{@$author.image32}</a>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">
					<span class="icon icon16 fa-star-o jsTooltip" title="{lang}chat.messageType.be.bastelstu.chat.messageType.team.tooltip{/lang}"></span>
					<a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">
						{@$author.coloredUsername}
					</a>
				</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<div class="chatMessage htmlContent">{@$message.payload.formattedMessage}</div>
		</div>
		<ul class="buttonGroup buttonList smallButtons">
			{/literal}
			{if $__wcf->session->getPermission('mod.chat.canDelete')}
				{ldelim}include file=$t.DeleteButton}
			{/if}
			{literal}
		</ul>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-plain" data-template-includes="DeleteButton">
	<div class="chatMessageContainer">
		<div class="chatMessageSide">
			<div class="chatUserAvatar jsUserActionDropdown" data-user-id="{$author.userID}">
				<a href="{$author.link}">{@$author.image32}</a>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">
					<a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">
						{@$author.coloredUsername}
					</a>
				</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<div class="chatMessage htmlContent">{@$message.payload.formattedMessage}</div>
		</div>
		<ul class="buttonGroup buttonList smallButtons">
			{/literal}
			{if $__wcf->session->getPermission('mod.chat.canDelete')}
				{ldelim}include file=$t.DeleteButton}
			{/if}
			{literal}
		</ul>
	</div>
</script>

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-color">
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

<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="be-bastelstu-chat-messageType-tombstone">
	<div class="chatMessageContainer inline">
		<div class="chatMessageSide">
			<div class="chatUserAvatar jsUserActionDropdown" data-user-id="{$author.userID}">
				<a href="{$author.link}">{@$author.image24}</a>
			</div>
			<time><a href="{$message.link}">{$message.formattedTime}</a></time>
		</div>
		<div class="chatMessageContent">
			<div class="chatMessageHeader">
				<span class="username">
					<a href="{$author.link}" class="jsUserActionDropdown" data-user-id="{$author.userID}">
						{@$author.coloredUsername}
					</a>
				</span>
				<small class="separatorLeft">
					<time><a href="{$message.link}">{$message.formattedTime}</a></time>
				</small>
			</div>
			<span class="chatMessageIcon">
				<span class="icon icon16 fa-trash"></span>
			</span>
			<div class="chatMessage">
				{lang}chat.messageType.be.bastelstu.chat.messageType.tombstone.message{/lang}
			</div>
		</div>
	</div>
</script>
{/literal}
{event name='messageTypes'}
