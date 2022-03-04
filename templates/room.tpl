{assign var='pageTitle' value=$room->getTitle()}

{capture assign='sidebarRight'}
	<section id="chatUserList" class="box chatUserList">
		<h2 class="boxTitle">{lang}chat.room.userList{/lang}</h2>

		<div class="boxContent">
			<ul></ul>
		</div>
	</section>
{/capture}

{capture assign='headerNavigation'}
	{if $room->canSeeLog()}
		<li>
			<a href="{link controller='Log' application='chat' object=$room}{/link}" title="{lang}chat.log.title{/lang}" class="jsTooltip">
				<span class="icon icon16 fa-tasks"></span> <span class="invisible">{lang}chat.log.title{/lang}</span>
			</a>
		</li>
	{/if}
	<li>
		<a href="{link}{/link}" title="{lang}chat.room.button.leave{/lang}" class="jsTooltip chatLeaveButton">
			<span class="icon icon16 fa-power-off"></span> <span class="invisible">{lang}chat.room.button.leave{/lang}</span>
		</a>
	</li>
{/capture}

{capture assign='__pageDataAttributes'}data-room-id="{@$room->roomID}"{/capture}

{include file='header'}

{if $room->getTopic()}
	<div class="chatRoomTopic">
		<span class="icon icon16 fa-times pointer jsDismissRoomTopicButton"></span>
		{@$room->getTopic()}
	</div>
{/if}

<div id="chatMessageStream" class="section">
	<div class="infoMessages">
		<div id="chatConnectionWarning" class="warning" style="display: none;">
			{lang}chat.connection.warning{/lang}
		</div>
		<div class="activityInfo info">
			{lang}chat.stream.activity{/lang}
		</div>
	</div>

	<div class="scrollContainer">
		<ul>
		</ul>
	</div>
</div>

<div id="chatInputContainer">
	<div>
		{if $__wcf->getSession()->getPermission('user.chat.canAttach')}
			<div class="chatAttachButton">
				<span id="chatAttachmentUploadButton" class="button small" title="{lang}wcf.attachment.attachments{/lang}">
					<span class="icon icon16 fa-paperclip"></span>
					<span class="icon icon24 fa-paperclip"></span>
				</span>
			</div>
		{/if}
		<div class="chatInputWrapper">
			<textarea maxlength="{CHAT_MAX_LENGTH}" class="long"></textarea>
			<span id="chatQuickSettings">
				<span class="icon icon24 fa-ellipsis-v"></span>
			</span>
		</div>
	</div>
	<small class="innerError" style="display: none"></small>
	<span class="charCounter dimmed"></span>
</div>

{assign var=smileyCategories value=$__wcf->getSmileyCache()->getVisibleCategories()}
{include file='quickSettings' application='chat'}
{include file='smileyPicker' application='chat'}

{include file='errorDialog' application='chat'}
{include file='messageTypes' application='chat'}
{include file='userList' application='chat'}
{include file='userListDropdownMenuItems' application='chat'}
{include file='__attachmentDialog' application='chat'}

{if !ENABLE_DEBUG_MODE}{js application='wcf' file='Bastelstu.be.Chat'}{/if}
<script data-relocate="true">
	require([ 'Language', 'Bastelstu.be/Chat/Ui/ErrorDialog' ], function (Language, ErrorDialog) {
		Language.addObject({
			'chat.connection.warning': '{lang __literal=true}chat.connection.warning{/lang}',
			'chat.error.hcf': '{lang __literal=true}chat.error.hcf{/lang}',
			'chat.error.initialization': '{lang __literal=true}chat.error.initialization{/lang}',
			'chat.error.triggerNotFound': '{lang __literal=true}chat.error.triggerNotFound{/lang}',
			'chat.error.invalidParameters': '{lang __literal=true}chat.error.invalidParameters{/lang}',
			'chat.notification.title': '{lang __literal=true}chat.notification.title{/lang}',
			'chat.user.autoAway': '{lang __literal=true}chat.user.autoAway{/lang}',
			{event name='language'}
		})

		const config = {@$config}

		let extraModules = [
			{if $extraModules|isset}{@$extraModules}{/if}
		]
		extraModules = extraModules.concat(Object.values(config.commands).map(item => item.module))
		extraModules = extraModules.concat(Object.values(config.messageTypes).map(item => item.module))
		extraModules = extraModules.concat(Array.from(elBySelAll('#chatQuickSettingsNavigation .button[data-module]')).map(item => item.dataset.module))

		require([ 'Bastelstu.be/Chat', 'Bastelstu.be/Chat/Helper' ].concat(extraModules), function (Chat, Helper, ...trash) {
			{event name='beforeInit'}

			Promise
			.resolve(new Chat({$room->roomID}, Helper.deepFreeze(config)))
			.then(function (chat) {
				const promises = new Set()

				{event name='beforeBootstrap'}

				return Promise.all(promises).then(chat.bootstrap.bind(chat))
			})
			.then(function (chat) {
				{event name='afterBootstrap'}

				require([ 'Bastelstu.be/Chat/BoxRoomList' ], function (BoxRoomList) {
					chat.registerMessageSink({
						ingest: function (messages) {
							const updateList = messages.some(message => message.objectType === 'be.bastelstu.chat.messageType.join' || message.objectType === 'be.bastelstu.chat.messageType.leave')

							if (updateList) {
								BoxRoomList.updateBoxes()
							}
						}
					})
				})

				elBySelAll('.chatLeaveButton', document, function (button) {
					button.addEventListener('click', function (event) {
						event.preventDefault()
						chat.bottle.container.Room.leave().then(function () {
							button.click()
						}, function () {
							button.click()
						})
					}, {
						once: true
					})
				})
			},
			function (err) {
				console.error('Chat', err)
				new ErrorDialog(Language.get('chat.error.initialization', { err }))
			})
		}, function (err) {
			console.error('Chat', err)
			new ErrorDialog(Language.get('chat.error.initialization', { err }))
		})
	})
</script>

{include file='footer'}
