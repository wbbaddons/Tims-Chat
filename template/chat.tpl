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
						'chat.general.query': '{lang}chat.general.query{/lang}',
						'chat.general.kick': '{lang}chat.general.kick{/lang}',
						'chat.general.ban': '{lang}chat.general.ban{/lang}',
						'chat.general.profile': '{lang}chat.general.profile{/lang}',
						'chat.general.notify.title': '{lang}chat.general.notify.title{/lang}',
						'chat.general.privateChannelTopic': '{lang}chat.general.privateChannelTopic{/lang}',
						'chat.general.closePrivateChannel': '{lang}chat.general.closePrivateChannel{/lang}',
						'chat.general.closeTopic': '{lang}chat.general.closeTopic{/lang}',
						'chat.error.onMessageLoad': '{@"chat.error.onMessageLoad"|language|encodeJS}',
						'chat.error.duplicateTab': '{lang}chat.error.duplicateTab{/lang}',
						'chat.error.join': '{lang}chat.error.join{/lang}',
						'chat.error.reload': '{lang}chat.error.reload{/lang}'
					});
					
					// Boot the chat
					{if MODULE_SMILEY}WCF.TabMenu.init();{/if}
					new WCF.Message.Smilies();
					{capture assign='messageTemplate'}{include application='chat' file='message'}{/capture}
					{capture assign='userTemplate'}{include application='chat' file='userListUser'}{/capture}
					
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
						new WCF.Template('{@$userTemplate|encodeJS}')
					);
					
					{event name='afterInit'}
					
					$('#timsChatCopyright').click(function (event) {
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
	
	<div id="timsChatTopic" class="container{if $room->topic|language === ''} empty{/if}">
		<span class="icon icon16 icon-remove jsTopicCloser jsTooltip" title="{lang}chat.general.closeTopic{/lang}"></span>
		<span class="topic">{$room->topic|language}</span>
	</div>
	
	<div id="privateChannelsMenu">
		<ul>
			<li id="privateChannel0" class="privateChannel active" data-private-channel-id="0">
				<span class="userAvatar framed small">
					<span class="icon icon16 icon-comment-alt jsTooltip" title="{lang}chat.general.room{/lang}"></span>
				</span>
				<span class="userAvatar framed large">
					<span class="icon icon32 icon-comment-alt jsTooltip" title="{lang}chat.general.room{/lang}"></span>
				</span>
			</li>
		</ul>
	</div>
	
	<div id="timsChatMessageContainer0" class="timsChatMessageContainer marginTop container active" data-user-id="0">
		<p class="error noJsOnly" style="display: none;">{lang}chat.general.noJs{/lang}</p>
		<ul>
		</ul>
	</div>
	
	<form id="timsChatForm" action="{link application='chat' controller='Chat' action='Send'}{/link}" method="post">
		<fieldset>
			<dl class="wide" id="timsChatInputContainer">
				<dd>
					<input id="timsChatInput" accesskey="w" type="text" class="inputText long" name="text" autocomplete="off" maxlength="{@CHAT_MAX_LENGTH}" disabled="disabled" placeholder="{lang}chat.general.submit.default{/lang}" />
					<small class="innerError" style="display: none;">Lorem ipsum dolor sit amet.</small>
				</dd>
			</dl>
		</fieldset>
		<button type="submit" class="invisible" accesskey="s"></button>
	</form>

	{if MODULE_SMILEY && $smileyCategories|count}
		{include file='messageFormSmilies' wysiwygSelector=''}
	{/if}
	
	<nav id="timsChatOptions" class="marginTop jsMobileNavigation buttonGroupNavigation">
		<span class="invisible">{lang}chat.general.controls{/lang}</span>
		<ul class="smallButtons buttonGroup">
			<li><a id="timsChatAutoscroll" accesskey="d" class="button active timsChatToggle jsTooltip" title="{lang}chat.general.scroll{/lang}" data-status="1"><span class="icon icon16 icon-arrow-down"></span><span class="invisible">{lang}chat.general.scroll{/lang}</span></a></li>{*
			*}<li><a id="timsChatFullscreen" accesskey="f" class="button timsChatToggle jsTooltip" title="{lang}chat.general.fullscreen{/lang}" data-status="0"><span class="icon icon16 icon-fullscreen"></span><span class="invisible">{lang}chat.general.fullscreen{/lang}</span></a></li>{*
			*}<li><a id="timsChatNotify" accesskey="n" class="button timsChatToggle jsTooltip" title="{lang}chat.general.notify{/lang}" data-status="0"><span class="icon icon16 icon-bell-alt"></span><span class="invisible">{lang}chat.general.notify{/lang}</span></a></li>{*
			*}<li{if !MODULE_SMILEY || !$smileyCategories|count} style="display: none;"{/if}><a id="timsChatSmilies" accesskey="e" class="button{if ENABLE_SMILIES_DEFAULT_VALUE} active{/if} timsChatToggle jsTooltip" title="{lang}chat.general.smilies{/lang}" data-status="{@ENABLE_SMILIES_DEFAULT_VALUE}"><span class="icon icon16 icon-smile"></span><span class="invisible">{lang}chat.general.smilies{/lang}</span></a></li>{*
			*}<li><a id="timsChatClear" class="button jsTooltip" title="{lang}chat.general.clear{/lang}"><span class="icon icon16 icon-remove"></span><span class="invisible">{lang}chat.general.clear{/lang}</span></a></li>{*
			*}<li><a id="timsChatMark" class="button timsChatToggle jsTooltip" title="{lang}chat.general.mark{/lang}" data-status="0"><span class="icon icon16 icon-check"></span><span class="invisible">{lang}chat.general.mark{/lang}</span></a></li>
		</ul>
	</nav>
	
	{include file='footer' sandbox=false}
</body>
</html>
