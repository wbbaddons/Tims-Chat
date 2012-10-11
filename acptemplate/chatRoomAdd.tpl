{include file='header'}

<script type="text/javascript" src="{@$__wcf->getPath('wcf')}js/WCF.ACL.js"></script>
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Icon.addObject({
			'wcf.icon.delete': '{@$__wcf->getPath('wcf')}icon/delete.svg',
			'wcf.icon.user': '{@$__wcf->getPath('wcf')}icon/user.svg',
			'wcf.icon.users': '{@$__wcf->getPath('wcf')}icon/users.svg'
		});
		
		new WCF.ACL.List($('#groupPermissions'), {@$objectTypeID}, ''{if $roomID|isset}, {@$roomID}{/if});
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.chat.room.{$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.form.{$action}.success{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='ChatRoomList'}{/link}" title="{lang}wcf.acp.menu.link.chat.room.list{/lang}" class="button"><img src="{@$__wcf->getPath('wcf')}icon/list.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.menu.link.chat.room.list{/lang}</span></a></li>
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='ChatRoomAdd'}{/link}{else}{link controller='ChatRoomEdit'}{/link}{/if}">
	<div class="container containerPadding sortableListContainer marginTop shadow">
		<fieldset>
			<legend>{lang}wcf.acp.chat.room.data{/lang}</legend>
			
			<dl{if $errorField == 'title'} class="formError"{/if}>
				<dt><label for="title">{lang}wcf.acp.chat.room.title{/lang}</label></dt>
				<dd>
					<input type="text" id="title" name="title" value="{$title}" autofocus="autofocus" class="long" />
					{if $errorField == 'title'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.chat.room.title.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{include file='multipleLanguageInputJavascript' elementIdentifier='title'}
			
			<dl{if $errorField == 'topic'} class="formError"{/if}>
				<dt><label for="topic">{lang}wcf.acp.chat.room.topic{/lang}</label></dt>
				<dd>
					<input type="text" id="topic" name="topic" value="{$topic}" class="long" />
					{if $errorField == 'topic'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.chat.room.topic.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{include file='multipleLanguageInputJavascript' elementIdentifier='topic'}
			
			<dl id="groupPermissions">
				<dt>{lang}wcf.acp.acl.permissions{/lang}</dt>
				<dd></dd>
			</dl>
		</fieldset>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
 		{if $roomID|isset}<input type="hidden" name="id" value="{@$roomID}" />{/if}
	</div>
</form>

{include file='footer'}