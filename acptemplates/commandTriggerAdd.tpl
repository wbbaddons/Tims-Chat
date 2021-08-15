{include file='header' pageTitle='chat.acp.command.trigger.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}chat.acp.command.trigger.{$action}{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link application='chat' controller='CommandTriggerList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}chat.acp.command.trigger.list{/lang}</span></a></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link application='chat' controller='CommandTriggerAdd'}{/link}{else}{link application='chat' controller='CommandTriggerEdit' id=$triggerID}{/link}{/if}">
	<div class="section">
		<div class="section">
			<dl{if $errorField == 'commandTrigger'} class="formError"{/if}>
				<dt><label for="commandTrigger">{lang}chat.acp.command.trigger{/lang}</label></dt>
				<dd>
					<input type="text" id="commandTrigger" name="commandTrigger" value="{$commandTrigger}" autofocus class="medium">
					{if $errorField == 'commandTrigger'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}chat.acp.command.trigger.commandTrigger.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>

			<dl{if $errorField == 'className'} class="formError"{/if}>
				<dt><label for="className">{lang}chat.acp.command.className{/lang}</label></dt>
				<dd>
					<select id="className" name="className">
						{foreach from=$availableCommands item=$command}
							<option value="{$command->className}"{if $command->className === $className} selected{/if}>{$command->className}</option>
						{/foreach}
					</select>

					{if $errorField == 'className'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}chat.acp.command.trigger.className.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		</div>
	</div>

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}

