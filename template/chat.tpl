{include file='documentHeader'}

<head>
	<title>{$room} - {lang}chat.general.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
	{include file='javascriptInclude' application='chat'}
	<script type="text/javascript">
		//<![CDATA[
			(function ($, window) {
				$(function(){
					WCF.Language.addObject({
						'chat.general.query': '{lang}chat.general.query{/lang}',
						'chat.general.kick': '{lang}chat.general.kick{/lang}',
						'chat.general.ban': '{lang}chat.general.ban{/lang}',
						'chat.general.profile': '{lang}chat.general.profile{/lang}',
						'chat.general.notify.title': '{lang}chat.general.notify.title{/lang}',
						'chat.general.error.onMessageLoad': '{lang}chat.general.error.onMessageLoad{/lang}'
					});
					
					{event name='beforeInit'}
					
					// Boot the chat
					{if MODULE_SMILEY}WCF.TabMenu.init();{/if}
					new WCF.Message.Smilies();
					{capture assign='messageTemplate'}{include application='chat' file='message'}{/capture}
					{capture assign='userTemplate'}{include application='chat' file='userListUser'}{/capture}
					
					window.chat = new be.bastelstu.Chat(
						{
							reloadTime: {@CHAT_RELOADTIME},
							unloadURL: '{link application="chat" controller="Leave"}{/link}',
							messageURL: '{link application="chat" controller="NewMessages"}{/link}',
							socketIOPath: '{@CHAT_SOCKET_IO_PATH|encodeJS}'
						}, 
						new WCF.Template('{ldelim}$title} - {"chat.general.title"|language|encodeJS} - {PAGE_TITLE|language|encodeJS}'),
						new WCF.Template('{@$messageTemplate|encodeJS}'),
						new WCF.Template('{@$userTemplate|encodeJS}')
					);
					
					{event name='afterInit'}
					
					// show the last X messages
					window.chat.handleMessages([
						{implode from=$newestMessages item='message'}{@$message->jsonify()}{/implode}
					]);
					
					// enable user-interface
					$('#timsChatInput').enable().jCounter().focus();
					
					$('#timsChatCopyright').click(function (event) {
						event.preventDefault();
						if (!$.wcfIsset('timsChatCopyrightDialog')) $('<div id="timsChatCopyrightDialog"></div>').appendTo('body');
						$('#timsChatCopyrightDialog').load('{link application="chat" controller="Copyright"}{/link}').wcfDialog({
							title: '<img width="246" height="90" alt="" src="{$__wcf->getPath("chat")|encodeJS}images/chatLogo.png"> {if SHOW_VERSION_NUMBER} {PACKAGE_VERSION}{/if}'
						});
					});
					
					$('#chatLogLink').click(function (event) {
						event.preventDefault();
						
						be.bastelstu.Chat.Log.loadOverlay();
					});
				});
			})(jQuery, this);
		//]]>
	</script>
	
	<style type="text/css">
		.timsChatMessage::before {
			content: "";
		}
		
		{assign var='type' value='\chat\data\message\Message::TYPE_'}
		.timsChatMessage{$type|concat:'JOIN'|constant}::before {
			content: "\f090";
		}
		
		.timsChatMessage{$type|concat:'LEAVE'|constant}::before {
			content: "\f08b";
		}
		
		.timsChatMessage{$type|concat:'INFORMATION'|constant}::before {
			content: "\f05a";
		}
		
		.timsChatMessage{$type|concat:'ERROR'|constant}::before {
			content: "\f05e";
		}
	</style>
</head>

<body id="tpl{$templateName|ucfirst}">
	{capture assign='sidebar'}{include application='chat' file='sidebar'}{/capture}
	{capture assign='headerNavigation'}{include application='chat' file='navigationInclude'}{/capture}
	{include file='header' sandbox=false sidebarOrientation='right'}
	
	<div>
		<p id="timsChatTopic" class="container{if $room->topic|language === ''} empty{/if}">{$room->topic|language}</p>
		
		<div id="timsChatMessageContainer" class="timsChatMessageContainer marginTop container active">
			<p class="error noJsOnly" style="display: none;">{lang}chat.general.noJs{/lang}</p>
			<ul>
			</ul>
		</div>
		
		<form id="timsChatForm" action="{link application='chat' controller='Chat' action='Send'}{/link}" method="post">
			<fieldset>
				<dl class="wide" id="timsChatInputContainer">
					<dd>
						<input id="timsChatInput" accesskey="w" type="text" class="inputText long" name="text" autocomplete="off" maxlength="{@CHAT_MAX_LENGTH}" disabled="disabled" required="required" placeholder="{lang}chat.general.submit.default{/lang}" />
						<small class="innerError" style="display: none;">Lorem ipsum dolor sit amet.</small>
					</dd>
				</dl>
			</fieldset>
		</form>
		
		<div id="timsChatControls" class="marginTop">
			{if MODULE_SMILEY}
				<div class="tabMenuContainer">
					{include file='messageFormSmilies' wysiwygSelector=''}
				</div>
			{/if}
			<nav id="timsChatOptions">
				<span class="invisible">{lang}chat.general.controls{/lang}</span>
				<ul class="smallButtons buttonGroup">
					<li><a id="timsChatAutoscroll" accesskey="d" class="button active timsChatToggle jsTooltip" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="1"><span class="icon icon16 icon-arrow-down"></span><span>{lang}chat.general.scroll{/lang}</span></a></li>{*
					*}<li><a id="timsChatFullscreen" accesskey="f" class="button timsChatToggle jsTooltip" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="0"><span class="icon icon16 icon-fullscreen"></span><span>{lang}chat.general.fullscreen{/lang}</span></a></li>{*
					*}<li><a id="timsChatNotify" accesskey="n" class="button timsChatToggle jsTooltip" title="{lang}wcf.global.button.enable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="0"><span class="icon icon16 icon-bell-alt"></span><span>{lang}chat.general.notify{/lang}</span></a></li>{*
					*}<li{if !MODULE_SMILEY} style="display: none;"{/if}><a id="timsChatSmilies" accesskey="e" class="button{if ENABLE_SMILIES_DEFAULT_VALUE} active{/if} timsChatToggle jsTooltip" title="{lang}wcf.global.button.{if ENABLE_SMILIES_DEFAULT_VALUE}dis{else}en{/if}able{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="{@ENABLE_SMILIES_DEFAULT_VALUE}"><span class="icon icon16 icon-{if ENABLE_SMILIES_DEFAULT_VALUE}circle-blank{else}off{/if}"></span><span>{lang}chat.general.smilies{/lang}</span></a></li>{*
					*}<li><a id="timsChatClear" class="button"><span class="icon icon16 icon-remove"></span><span>{lang}chat.general.clear{/lang}</span></a></li>{*
					*}<li><a id="timsChatMark" class="button timsChatToggle jsTooltip" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="0"><span class="icon icon16 icon-check"></span><span>{lang}chat.general.mark{/lang}</span></a></li>
				</ul>
			</nav>
		</div>
	</div>
	
	{include file='footer' sandbox=false}
</body>
</html>
