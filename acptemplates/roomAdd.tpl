{include file='header' pageTitle='chat.acp.room.'|concat:$action}

{include file='aclPermissions'}

{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}
{include file='multipleLanguageInputJavascript' elementIdentifier='topic' forceSelection=false}

{if $roomID|isset}
	{include file='aclPermissionJavaScript' containerID='aclContainer' objectTypeID=$aclObjectTypeID objectID=$roomID}
{else}
	{include file='aclPermissionJavaScript' containerID='aclContainer' objectTypeID=$aclObjectTypeID}
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}chat.acp.room.{$action}{/lang}</h1>
		{if $action == 'edit'}<p class="contentHeaderDescription">{$room->getTitle()}</p>{/if}
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link application='chat' controller='RoomList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}chat.acp.room.list{/lang}</span></a></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link application='chat' controller='RoomAdd'}{/link}{else}{link application='chat' controller='RoomEdit' id=$roomID}{/link}{/if}">
	<div class="section">
		<div class="section">
			<dl{if $errorField == 'title'} class="formError"{/if}>
				<dt><label for="title">{lang}wcf.global.title{/lang}</label></dt>
				<dd>
					<input type="text" id="title" name="title" value="{$i18nPlainValues['title']}" autofocus class="medium">
					{if $errorField == 'title'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{elseif $errorType == 'multilingual'}
								{lang}wcf.global.form.error.multilingual{/lang}
							{else}
								{lang}chat.acp.room.title.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>

			<dl{if $errorField == 'topic'} class="formError"{/if}>
				<dt><label for="topic">{lang}chat.acp.room.topic{/lang}</label></dt>
				<dd>
					<input type="text" id="topic" name="topic" value="{$i18nPlainValues['topic']}" class="long">

					{if $errorField == 'topic'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}chat.acp.room.topic.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>

			<dl{if $errorField == 'topicUseHtml'} class="formError"{/if}>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="topicUseHtml" value="1"{if $topicUseHtml} checked{/if}> {lang}chat.acp.room.topicUseHtml{/lang}</label>
				</dd>
			</dl>

			<dl{if $errorField == 'userLimit'} class="formError"{/if}>
				<dt><label for="userLimit">{lang}chat.acp.room.userLimit{/lang}</label></dt>
				<dd>
					<input type="number" id="userLimit" name="userLimit" value="{$userLimit}" min="0" class="medium">

					{if $errorField == 'userLimit'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}chat.acp.room.userLimit.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		</div>

		<div class="section">
			<dl id="aclContainer">
				<dt>{lang}wcf.acl.permissions{/lang}</dt>
				<dd></dd>
			</dl>
		</div>
	</div>

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}

