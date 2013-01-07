{include file='documentHeader'}

<head>
	<title>{$room} - {lang}wcf.chat.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
	<style type="text/css">
		#timsChatCopyrightDialog {
			background-image: url("{link controller='Chat' action='Copyright' sheep=1}{/link}");
		}
		
		{assign var='type' value='\wcf\data\chat\message\ChatMessage::TYPE_'}
		.timsChatMessage{$type|concat:'JOIN'|constant}, .timsChatMessage{$type|concat:'LEAVE'|constant},
		.timsChatMessage{$type|concat:'INFORMATION'|constant}, .timsChatMessage{$type|concat:'ERROR'|constant} {
			background-position: left top;
			background-repeat: no-repeat;
			background-size: 16px 16px;
		}
		
		.timsChatMessage{$type|concat:'JOIN'|constant} {
			background-image: url({icon size='S'}circleArrowRight{/icon});
		}
		
		.timsChatMessage{$type|concat:'LEAVE'|constant} {
			background-image: url({icon size='S'}circleArrowLeft{/icon});
		}
		
		.timsChatMessage{$type|concat:'INFORMATION'|constant} {
			background-image: url({icon size='S'}systemInfo{/icon});
		}
		
		.timsChatMessage{$type|concat:'ERROR'|constant} {
			background-image: url({icon size='S'}systemError{/icon});
		}
	</style>
</head>

<body id="tpl{$templateName|ucfirst}">
{capture assign='sidebar'}{include file='chatSidebar'}{/capture}
{capture assign='headerNavigation'}{include file='chatNavigationInclude'}{/capture}
{include file='header' sandbox=false sidebarOrientation='right'}

<div id="timsChatRoomContent">
	<div id="timsChatTopic" class="container"{if $room->topic|language === ''} style="display: none;"{/if}>{$room->topic|language}</div>
	<fieldset>
		<div class="timsChatMessageContainer container box shadow1">
			<ul>
				<li class="error">{lang}wcf.chat.noJs{/lang}</li>
			</ul>
		</div>
	</fieldset>
	
	<form id="timsChatForm" action="{link controller='Chat' action='Send'}{/link}" method="post">
		<input id="timsChatInput" accesskey="w" type="text" class="inputText long" name="text" autocomplete="off" maxlength="{@CHAT_MAX_LENGTH}" disabled="disabled" required="required" placeholder="{lang}wcf.chat.submit.default{/lang}" />
	</form>
	
	<div id="timsChatControls" class="marginTop">
		{if MODULE_SMILEY}
			{capture assign=__defaultSmilies}
				{include file='__messageFormSmilies' smilies=$defaultSmilies}
			{/capture}
			
			<div id="smilies" class="smiliesContent tabMenuContent container {if $smileyCategories|count} tabMenuContainer{/if}" data-store="activeTabMenuItem" data-active="smilies-default">
				{if $smileyCategories|count}
					<nav class="menu">
						<ul>
							<li><a href="#smilies-default">{lang}wcf.smilies.default{/lang}</a></li>
							{foreach from=$smileyCategories item=smileyCategory}
								<li><a href="#smilies-{@$smileyCategory->smileyCategoryID}" data-smiley-category-id="{@$smileyCategory->smileyCategoryID}">{$smileyCategory->title|language}</a></li>
							{/foreach}
						</ul>
					</nav>
					
					<div id="smilies-default" class="hidden">
						{@$__defaultSmilies}
					</div>
					
					{foreach from=$smileyCategories  item='smileyCategory'}
						<div id="smilies-{$smileyCategory->smileyCategoryID}" class="hidden"></div>
					{/foreach}
				{else}
					{@$__defaultSmilies}
				{/if}
			</div>
		{/if}
		<nav id="timsChatOptions">
			<ul class="smallButtons">
				<li>
					<a id="timsChatAutoscroll" accesskey="d" class="timsChatToggle jsTooltip button" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="1">
						<img alt="" src="{icon size='S'}enabled{/icon}" /> <span>{lang}wcf.chat.scroll{/lang}</span>
					</a>
				</li>
				<li>
					<a id="timsChatFullscreen" accesskey="f" class="timsChatToggle jsTooltip button" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="0">
						<img alt="" src="{icon size='S'}disabled{/icon}" /> <span>{lang}wcf.chat.fullscreen{/lang}</span>
					</a>
				</li>
				<li>
					<a id="timsChatNotify" accesskey="n" class="timsChatToggle jsTooltip button" title="{lang}wcf.global.button.enable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="0">
						<img alt="" src="{icon size='S'}disabled{/icon}" /> <span>{lang}wcf.chat.notify{/lang}</span>
					</a>
				</li>
				<li{if !MODULE_SMILEY} style="display: none;"{/if}>
					<a id="timsChatSmilies" accesskey="e" class="timsChatToggle jsTooltip button" title="{lang}wcf.global.button.{if ENABLE_SMILIES_DEFAULT_VALUE}dis{else}en{/if}able{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="{@ENABLE_SMILIES_DEFAULT_VALUE}">
						<img alt="" src="{icon size='S'}{if ENABLE_SMILIES_DEFAULT_VALUE}en{else}dis{/if}abled{/icon}" /> <span>{lang}wcf.chat.smilies{/lang}</span>
					</a>
				</li>
				<li>
					<a id="timsChatClear" class="jsTooltip button" title="{lang}wcf.chat.clear.description{/lang}">
						<img alt="" src="{icon size='S'}delete{/icon}" /> <span>{lang}wcf.chat.clear{/lang}</span>
					</a>
				</li>
				<li>
					<a id="timsChatMark" class="jsTooltip button" title="{lang}wcf.chat.mark.description{/lang}">
						<img alt="" src="{icon size='S'}check{/icon}" /> <span>{lang}wcf.chat.mark{/lang}</span>
					</a>
				</li>
			</ul>
		</nav>
	</div>
	{include file='chatCopyright'}
</div>
{include file='chatJavascriptInclude'}
<script type="text/javascript">
	//<![CDATA[
		(function ($, window) {
			// remove noscript message
			$('.timsChatMessageContainer .error').remove();
			
			// populate templates
			be.bastelstu.WCF.Chat.titleTemplate = (new WCF.Template('{ldelim}$title} - {'wcf.chat.title'|language|encodeJS} - {PAGE_TITLE|language|encodeJS}')).compile();
			{capture assign='chatMessageTemplate'}{include file='chatMessage'}{/capture}
			be.bastelstu.WCF.Chat.messageTemplate = (new WCF.Template('{@$chatMessageTemplate|encodeJS}')).compile();
			
			// populate config
			be.bastelstu.WCF.Chat.config = {
				reloadTime: {@CHAT_RELOADTIME},
				unloadURL: '{link controller="Chat" action="Leave"}{/link}',
				messageURL: '{link controller="Chat" action="Message"}{/link}',
				socketIOPath: '{@CHAT_SOCKET_IO_PATH|encodeJS}'
			}
			WCF.Language.addObject({
				'wcf.chat.query': '{lang}wcf.chat.query{/lang}',
				'wcf.chat.kick': '{lang}wcf.chat.kick{/lang}',
				'wcf.chat.ban': '{lang}wcf.chat.ban{/lang}',
				'wcf.chat.profile': '{lang}wcf.chat.profile{/lang}',
				'wcf.chat.notify.title': '{lang}wcf.chat.notify.title{/lang}'
			});
			WCF.Icon.addObject({
				'be.bastelstu.wcf.chat.chat': '{icon}chat{/icon}'
			});
			{event name='shouldInit'}
			// Boot the chat
			WCF.TabMenu.init();
			new WCF.Message.Smilies();
			be.bastelstu.WCF.Chat.init();
			{event name='didInit'}
			
			// show the last X messages
			be.bastelstu.WCF.Chat.handleMessages([
				{implode from=$newestMessages item='message'}
					{@$message->jsonify()}
				{/implode}
			]);
			
			// enable user-interface
			$('#timsChatInput').enable().jCounter().focus();
			$('#timsChatCopyright').click(function (event) {
				event.preventDefault();
				if ($.wcfIsset('timsChatCopyrightDialog')) return WCF.showDialog('timsChatCopyrightDialog', { title: 'Tims Chat{if CHAT_SHOW_VERSION && $chatVersion|isset} {$chatVersion}{/if}' });
				var container = $('<fieldset id="timsChatCopyrightDialog"></fieldset>');
				container.load('{link controller='Chat' action='Copyright'}{/link}', function() {
					$('body').append(container);
					WCF.showDialog('timsChatCopyrightDialog', { title: 'Tims Chat{if CHAT_SHOW_VERSION && $chatVersion|isset} {$chatVersion}{/if}' });
				});
			});
		})(jQuery, this)
	//]]>
</script>

{include file='footer' sandbox=false}
</body>
</html>
