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
					
					be.bastelstu.Chat.init(
						{
							reloadTime: {@CHAT_RELOADTIME},
							messageURL: '{link application="chat" controller="NewMessages"}{/link}'
						}, 
						new WCF.Template('{literal}{if $newMessageCount}({#$newMessageCount}) {/if}{$title} - {/literal}{"chat.general.title"|language|encodeJS} - {PAGE_TITLE|language|encodeJS}'),
						new WCF.Template('{@$messageTemplate|encodeJS}'),
						new WCF.Template('{@$userTemplate|encodeJS}')
					);
					
					{event name='afterInit'}
					
					// show the last X messages
					be.bastelstu.Chat.handleMessages([
						{implode from=$newestMessages item='message'}{@$message->jsonify()}{/implode}
					]);
					
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
		/*<![CDATA[*/
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
		/*]]>*/
	</style>
</head>

<body id="tpl{$templateName|ucfirst}">
	{capture assign='sidebar'}{include application='chat' file='sidebar'}{/capture}
	{capture assign='headerNavigation'}{include application='chat' file='navigationInclude'}{/capture}
	{include file='header' sandbox=false sidebarOrientation='right'}
	
	<div>
		<div id="timsChatTopic" class="container{if $room->topic|language === ''} empty{/if}">{$room->topic|language}</div>
		
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
			<button type="submit" class="invisible" accesskey="s"></button>
		</form>

		{if MODULE_SMILEY && $smileyCategories|count}
			{include file='messageFormSmilies' wysiwygSelector=''}
		{/if}
		
		<nav id="timsChatOptions" class="marginTop">
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
	</div>
	
	{include file='footer' sandbox=false}
</body>
</html>
