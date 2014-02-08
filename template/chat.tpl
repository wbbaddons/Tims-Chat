{include file='documentHeader'}

<head>
	<title>{$room} - {lang}chat.general.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
	{include file='javascriptInclude' application='chat'}
	<script data-relocate="true">
		//<![CDATA[
			(function ($, window) {
				$(function(){
					WCF.Language.addObject({
						'chat.general.ban': '{lang}chat.general.ban{/lang}',
						'chat.general.closePrivateChannel': '{lang}chat.general.closePrivateChannel{/lang}',
						'chat.general.closeTopic': '{lang}chat.general.closeTopic{/lang}',
						'chat.general.notify.title': '{lang}chat.general.notify.title{/lang}',
						'chat.general.privateChannelTopic': '{lang}chat.general.privateChannelTopic{/lang}',
						'chat.general.profile': '{lang}chat.general.profile{/lang}',
						'chat.general.query': '{lang}chat.general.query{/lang}',
						'chat.general.whisper': '{lang}chat.general.whisper{/lang}',
						'chat.error.duplicateTab': '{lang}chat.error.duplicateTab{/lang}',
						'chat.error.join': '{lang}chat.error.join{/lang}',
						'chat.error.onMessageLoad': '{@"chat.error.onMessageLoad"|language|encodeJS}',
						'chat.error.reload': '{lang}chat.error.reload{/lang}',
						'chat.message.{$messageTypes[TYPE_ATTACHMENT]}': '{lang}chat.message.{$messageTypes[TYPE_ATTACHMENT]}{/lang}',
						'wcf.attachment.insert': '{lang}wcf.attachment.insert{/lang}',
						'wcf.attachment.delete.sure': '{lang}wcf.attachment.delete.sure{/lang}',
						'wcf.attachment.upload.error.invalidExtension': '{lang}wcf.attachment.upload.error.invalidExtension{/lang}',
						'wcf.attachment.upload.error.tooLarge': '{lang}wcf.attachment.upload.error.tooLarge{/lang}',
						'wcf.attachment.upload.error.reachedLimit': '{lang}wcf.attachment.upload.error.reachedLimit{/lang}',
						'wcf.attachment.upload.error.reachedRemainingLimit': '{lang}wcf.attachment.upload.error.reachedRemainingLimit{/lang}',
						'wcf.attachment.upload.error.uploadFailed': '{lang}wcf.attachment.upload.error.uploadFailed{/lang}',
						'wcf.global.button.upload': '{lang}wcf.global.button.upload{/lang}'
					});
					
					// Boot the chat
					{if MODULE_ATTACHMENT && $__wcf->session->getPermission('user.chat.canUploadAttachment')}
						new be.bastelstu.Chat.Attachment();
						new be.bastelstu.Chat.Action.Delete('wcf\\data\\attachment\\AttachmentAction', '#timsChatUploadDropdownMenu > li');
					{/if}
					
					WCF.TabMenu.init();
					
					{if MODULE_SMILEY}
						new WCF.Message.Smilies();
					{/if}
					
					{capture assign='messageTemplate'}{include application='chat' file='message'}{/capture}
					{capture assign='userTemplate'}{include application='chat' file='userListUser'}{/capture}
					{capture assign='userMenuTemplate'}{include application='chat' file='userListUserMenu'}{/capture}
					
					var config = {
						reloadTime: {@CHAT_RELOADTIME},
						messageURL: '{link application="chat" controller="NewMessages"}{/link}',
						installedCommands: [ {implode from=$commands item='command'}'{$command|encodeJS}'{/implode} ],
						messageTypes: { {implode from=$messageTypes key='name' item='messageType'}'{$name|substr:5|encodeJS}': '{$messageType|encodeJS}'{/implode} }
					};
					
					{event name='beforeInit'}
					
					be.bastelstu.Chat.init(
						{$roomID},
						config,
						new WCF.Template('{literal}{if $newMessageCount}({#$newMessageCount}) {/if}{$title} - {/literal}{"chat.general.title"|language|encodeJS} - {PAGE_TITLE|language|encodeJS}'),
						new WCF.Template('{@$messageTemplate|encodeJS}'),
						new WCF.Template('{@$userTemplate|encodeJS}'),
						new WCF.Template('{@$userMenuTemplate|encodeJS}')
					);
					
					{event name='afterInit'}
					
					$('#timsChatCopyright a').click(function (event) {
						event.preventDefault();
						if (!$.wcfIsset('timsChatCopyrightDialog')) $('<div id="timsChatCopyrightDialog"></div>').appendTo('body');
						$('#timsChatCopyrightDialog').load('{link application="chat" controller="Copyright"}{/link}').wcfDialog({
							title: '<img width="246" height="90" alt="" src="{$__wcf->getPath("chat")|encodeJS}images/chatLogo.png"> {if SHOW_VERSION_NUMBER} {PACKAGE_VERSION}{/if}'
						});
					});
				});
			})(jQuery, this);
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}">
	{capture assign='sidebar'}{include application='chat' file='sidebar'}{/capture}
	{include file='header' sandbox=false sidebarOrientation='right'}
	
	<div class="clearfix">
		<div id="timsChatTopic" class="container containerPadding marginTop{if $room->topic|language === ''} empty{/if}">
			<span id="timsChatTopicCloser" class="icon icon16 icon-remove jsTooltip" title="{lang}chat.general.closeTopic{/lang}"></span>
			<span class="topic">{$room->topic|language}</span>
		</div>
		
		<div id="timsChatMessageTabMenu" class="tabMenuContainer singleTab" data-active="timsChatMessageContainer0">
			<nav class="tabMenu">
				<ul>
					<li>
						<a id="timsChatMessageTabMenuAnchor0" href="{$__wcf->getAnchor('timsChatMessageContainer0')}" class="timsChatMessageTabMenuAnchor" data-user-id="0">
							<span class="icon icon16 icon-warning-sign notifyIcon"></span>{*
							*}<span class="userAvatar framed">
								<span class="icon icon16 icon-group"></span>
							</span>{*
							*}<span>{$room}</span>
						</a>
					</li>
				</ul>
			</nav>
			
			<div id="timsChatMessageContainer0" class="tabMenuContent timsChatMessageContainer container containerPadding active" data-user-id="0">
				<p class="error noJsOnly" style="display: none;">{lang}chat.general.noJs{/lang}</p>
				<ul></ul>
			</div>
		</div>
		
		<form id="timsChatForm" action="{link application='chat' controller='Chat' action='Send'}{/link}" method="post">
			<fieldset>
				<dl class="wide" id="timsChatInputContainer">
					<dt>
						{lang}chat.general.message{/lang}
					</dt>
					<dd>
						<input id="timsChatInput" accesskey="w" type="text" class="inputText long" name="text" autocomplete="off" maxlength="{@CHAT_MAX_LENGTH}" disabled="disabled" placeholder="{lang}chat.general.submit.default{/lang}" />
						<small class="innerError" style="display: none;">Lorem ipsum dolor sit amet.</small>
					</dd>
				</dl>
			</fieldset>
			<button type="submit" class="marginTop invisible" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
		</form>

		{if MODULE_SMILEY && $smileyCategories|count}
			{include file='messageFormSmilies' wysiwygSelector=''}
		{/if}
		
		<div id="timsChatOptions" class="marginTop">
			<span id="timsChatSmileyPopupButton" class="button smallButtons">
				<span class="icon icon16 icon-smile"></span>
				<span>{lang}chat.general.smilies{/lang}</span>
			</span>
			
			<nav class="jsMobileNavigation buttonGroupNavigation">
				<ul class="buttonGroup">
					<li>
						<a id="timsChatAutoscroll" accesskey="d" class="button active timsChatToggle jsTooltip" title="{lang}chat.general.scroll{/lang}" data-status="1">
							<span class="icon icon16 icon-arrow-down"></span>
							<span class="invisible">{lang}chat.general.scroll{/lang}</span>
						</a>
					</li>
					<li>
						<a id="timsChatFullscreen" accesskey="f" class="button timsChatToggle jsTooltip" title="{lang}chat.general.fullscreen{/lang}" data-status="0">
								<span class="icon icon16 icon-fullscreen"></span>
								<span class="invisible">{lang}chat.general.fullscreen{/lang}</span>
						</a>
					</li>
					<li>
						<a id="timsChatNotify" accesskey="n" class="button timsChatToggle jsTooltip" title="{lang}chat.general.notify{/lang}" data-status="0">
							<span class="icon icon16 icon-bell-alt"></span>
							<span class="invisible">{lang}chat.general.notify{/lang}</span>
						</a>
					</li>
					
					{if MODULE_SMILEY && $smileyCategories|count}
					<li>
						<a id="timsChatSmilies" accesskey="e" class="button{if ENABLE_SMILIES_DEFAULT_VALUE} active{/if} timsChatToggle jsTooltip" title="{lang}chat.general.smilies{/lang}" data-status="{@ENABLE_SMILIES_DEFAULT_VALUE}">
							<span class="icon icon16 icon-smile"></span>
							<span class="invisible">{lang}chat.general.smilies{/lang}</span>
						</a>
					</li>
					{/if}
					
					{if MODULE_ATTACHMENT && $__wcf->session->getPermission('user.chat.canUploadAttachment')}
						<li id="timsChatUploadContainer" class="dropdown" data-max-size="{$attachmentHandler->getMaxSize()}">
							<a id="timsChatUpload" class="dropdownToggle button jsTooltip" title="{lang}wcf.attachment.attachments{/lang}" data-toggle="timsChatUploadContainer">
									<span class="icon icon16 icon-paper-clip"></span>
									<span class="invisible">{lang}wcf.attachment.attachments{/lang}</span>
							</a>
							<ul id="timsChatUploadDropdownMenu" class="dropdownMenu">
								<li class="uploadButton" style="margin-top: 0;">
									<span><label for="timsChatUploadInput" class="pointer">{lang}wcf.global.button.upload{/lang}</label></span>
								</li>
							</ul>
						</li>
					{/if}
					
					<li>
						<a id="timsChatClear" class="button jsTooltip" title="{lang}chat.general.clear{/lang}">
							<span class="icon icon16 icon-remove"></span>
							<span class="invisible">{lang}chat.general.clear{/lang}</span>
						</a>
					</li>
					<li>
						<a id="timsChatMark" class="button timsChatToggle jsTooltip" title="{lang}chat.general.mark{/lang}" data-status="0">
							<span class="icon icon16 icon-check"></span>
							<span class="invisible">{lang}chat.general.mark{/lang}</span>
						</a>
					</li>
				</ul>
			</nav>
		</div>
	</div>
	
	{include file='footer' sandbox=false}
</body>
</html>
