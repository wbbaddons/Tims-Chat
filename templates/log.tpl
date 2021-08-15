{include file='header'}

{capture assign='sidebarRight'}
	<section class="box">
		<form method="post" action="{link controller='Log' application='chat' object=$room}{/link}">
			<h2 class="boxTitle">{lang}chat.log.jumpToDate{/lang}</h2>

			<div class="boxContent">
				<dl>
					<dt></dt>
					<dd>
						<input type="datetime" id="datetime" name="datetime" data-placeholder="{lang}chat.log.date{/lang}" value="{if $message}{$message->time|date:'c'}{/if}">
						{csrfToken}
					</dd>
				</dl>

				<div class="formSubmit">
					<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
				</div>
			</div>
		</form>
	</section>

	<section class="box">
		<h2 class="boxTitle">{lang}wcf.acp.box.boxController.be.bastelstu.chat.roomList{/lang}</h2>

		<div class="boxContent">
			<ol class="boxMenu">
				{foreach from=$roomList item='_room'}
					{if $_room->canSee() && $_room->canSeeLog()}
						<li{if $room->roomID === $_room->roomID} class="active"{/if}>
							<a href="{link controller='Log' application='chat' object=$_room}{/link}" class="boxMenuLink">
								<span class="boxMenuLinkTitle">{$_room->getTitle()}</span>
							</a>
						</li>
					{/if}
				{/foreach}
			</ol>
		</div>
	</section>
{/capture}


<div id="chatMessageStream" class="section">
	<div class="infoMessages">
		<p id="chatConnectionWarning" class="warning invisible">
			{lang}chat.connection.warning{/lang}
		</p>
	</div>

	<div class="scrollContainer">
		<ul>
		</ul>
	</div>
</div>

{include file='errorDialog' application='chat'}
{include file='messageTypes' application='chat'}

<script data-relocate="true">
	require([ 'Language', 'Bastelstu.be/Chat/Ui/ErrorDialog' ], function (Language, ErrorDialog) {
		Language.addObject({
			'chat.connection.warning': '{lang __literal=true}chat.connection.warning{/lang}',
			'chat.error.hcf': '{lang __literal=true}chat.error.hcf{/lang}',
			'chat.error.initialization': '{lang __literal=true}chat.error.initialization{/lang}',
			'chat.error.triggerNotFound': '{lang __literal=true}chat.error.triggerNotFound{/lang}',
			{event name='language'}
		})

		const config = {@$config}

		let extraModules = [
			{if $extraModules|isset}{$extraModules}{/if}
		]
		extraModules = extraModules.concat(Object.values(config.commands).map(item => item.module))
		extraModules = extraModules.concat(Object.values(config.messageTypes).map(item => item.module))

		require([ 'Bastelstu.be/Chat/Log', 'Bastelstu.be/Chat/Helper' ].concat(extraModules), function (ChatLog, Helper, ...trash) {
			{event name='beforeInit'}

			Promise
			.resolve(new ChatLog({ roomID: {$room->roomID}, messageID: {$messageID} }, Helper.deepFreeze(config)))
			.then(function (log) {
				const promises = new Set()

				{event name='beforeBootstrap'}

				return Promise.all(promises).then(log.bootstrap.bind(log))
			})
			.then(function (log) {
				{event name='afterBootstrap'}
			},
			function (err) {
				console.error('Chat.Log', err)
				new ErrorDialog(Language.get('chat.error.initialization', { err }))
			})
		}, function (err) {
			console.error('Chat', err)
			new ErrorDialog(Language.get('chat.error.initialization', { err }))
		})
	})
</script>

{include file='footer'}
