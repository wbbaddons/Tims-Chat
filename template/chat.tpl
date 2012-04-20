{include file='documentHeader'}

<head>
	<title>{$room} - {lang}wcf.chat.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
	<style type="text/css">
		@import url("{@$__wcf->getPath('wcf')}style/be.bastelstu.wcf.chat.css");
		#timsChatCopyrightDialog {
			background-image: url("{link controller='Chat' action='Copyright' sheep=1}{/link}");
			background-position: right 45px;
			background-repeat: no-repeat;
			min-height: 50%;
		}
		
		#timsChatUserList > li > a {
			background-image: url({icon size='S'}arrowRight{/icon});
			background-position: 15px center;
			background-repeat: no-repeat;
		}
		
		#timsChatUserList > li.activeMenuItem > a {
			background-image: url({icon size='S'}arrowDown{/icon});
		}
		
		{assign var='type' value='\wcf\data\chat\message\ChatMessage::TYPE_'}
		.timsChatMessage{$type|concat:'JOIN'|constant}, .timsChatMessage{$type|concat:'LEAVE'|constant},
		.timsChatMessage{$type|concat:'INFORMATION'|constant}, .timsChatMessage{$type|concat:'ERROR'|constant} {
			background-position: left top;
			background-repeat: no-repeat;
			background-size: 16px 16px;
		}
		
		.timsChatMessage{$type|concat:'JOIN'|constant} {
			background-image: url({icon size='S'}toRight1{/icon});
		}
		
		.timsChatMessage{$type|concat:'LEAVE'|constant} {
			background-image: url({icon size='S'}toLeft1{/icon});
		}
		
		.timsChatMessage{$type|concat:'INFORMATION'|constant} {
			background-image: url({icon size='S'}systemInfo{/icon});
		}
		
		.timsChatMessage{$type|concat:'ERROR'|constant} {
			background-image: url({icon size='S'}systemError{/icon});
		}
		
		.ajaxLoad {
			background-image: url({icon size='S'}spinner1{/icon});
		}
	</style>
</head>

<body id="tpl{$templateName|ucfirst}">
{capture assign='sidebar'}{include file='chatSidebar'}{/capture}
{capture assign='headerNavigation'}{include file='chatNavigationInclude'}{/capture}
{include file='header' sandbox=false sidebarOrientation='right'}

<div id="timsChatRoomContent">
	<div id="timsChatTopic" class="container box16"{if $room->topic|language === ''} style="display: none;"{/if}>{$room->topic|language}</div>
	<fieldset class="timsChatMessageContainer container box shadow1 containerPadding marginTop">
		<ul>
			<noscript><li class="error">{lang}wcf.chat.noJs{/lang}</li></noscript>
		</ul>
	</fieldset>
	
	<form id="timsChatForm" action="{link controller="Chat" action="Send"}{/link}" method="post">
		<input type="text" id="timsChatInput" class="inputText long" name="text" autocomplete="off" maxlength="{@CHAT_MAX_LENGTH}" disabled="disabled" required="required" placeholder="{lang}wcf.chat.submit.default{/lang}" />
	</form>
	
	<div id="timsChatControls" class="box24 marginTop container">
		{if MODULE_SMILEY}
			<div id="smileyList">
				<ul class="smilies">
					{foreach from=$smilies item='smiley'}
						<li>
							<img src="{$smiley->getURL()}" alt="{$smiley->smileyCode}" title="{$smiley->smileyTitle}" class="jsSmiley jsTooltip" />
						</li>
					{/foreach}
				</ul>
			</div>
		{/if}
		<nav id="timsChatOptions">
			<ul class="smallButtons">
				<li>
					<a id="timsChatAutoscroll" href="javascript:;" class="timsChatToggle jsTooltip button" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="1">
						<img alt="" src="{icon size='S'}enabled1{/icon}" /> <span>{lang}wcf.chat.scroll{/lang}</span>
					</a>
				</li>
				<li>
					<a id="timsChatNotify" href="javascript:;" class="timsChatToggle jsTooltip button" title="{lang}wcf.global.button.enable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="0">
						<img alt="" src="{icon size='S'}disabled1{/icon}" /> <span>{lang}wcf.chat.notify{/lang}</span>
					</a>
				</li>
				<li{if !MODULE_SMILEY} style="display: none;"{/if}>
					<a id="timsChatSmilies" href="javascript:;" class="timsChatToggle jsTooltip button" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="1">
						<img alt="" src="{icon size='S'}enabled1{/icon}" /> <span>{lang}wcf.chat.smilies{/lang}</span>
					</a>
				</li>
				<li>
					<a id="timsChatClear" href="javascript:;" class="jsTooltip button" title="{lang}wcf.chat.clear.description{/lang}">
						<img alt="" src="{icon size='S'}delete1{/icon}" /> <span>{lang}wcf.chat.clear{/lang}</span>
					</a>
				</li>
				<li>
					<a id="timsChatMark" href="javascript:;" class="jsTooltip button" title="{lang}wcf.chat.mark.description{/lang}">
						<img alt="" src="{icon size='S'}check1{/icon}" /> <span>{lang}wcf.chat.mark{/lang}</span>
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
			// populate templates
			be.bastelstu.WCF.Chat.titleTemplate = (new WCF.Template('{ldelim}$title} - {'wcf.chat.title'|language|encodeJS} - {PAGE_TITLE|language|encodeJS}')).compile();
			{capture assign='chatMessageTemplate'}{include file='chatMessage'}{/capture}
			be.bastelstu.WCF.Chat.messageTemplate = (new WCF.Template('{@$chatMessageTemplate|encodeJS}')).compile();
			
			// populate config
			be.bastelstu.WCF.Chat.config = {
				reloadTime: {@CHAT_RELOADTIME},
				unloadURL: '{link controller='Chat' action='Leave'}{/link}'
			}
			WCF.Language.addObject({
				'wcf.chat.query': '{lang}wcf.chat.query{/lang}',
				'wcf.chat.kick': '{lang}wcf.chat.kick{/lang}',
				'wcf.chat.ban': '{lang}wcf.chat.ban{/lang}',
				'wcf.chat.profile': '{lang}wcf.chat.profile{/lang}',
				'wcf.chat.newMessages': '{lang}wcf.chat.newMessages{/lang}'
			});
			WCF.Icon.addObject({
				'be.bastelstu.wcf.chat.chat': '{icon size='L'}chat1{/icon}'
			});
			{event name='shouldInit'}
			// Boot the chat
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
				if ($.wcfIsset('timsChatCopyrightDialog')) return WCF.showDialog('timsChatCopyrightDialog', { title: 'Tims Chat{if CHAT_SHOW_VERSION} {$chatVersion}{/if}' });
				var container = $('<div id="timsChatCopyrightDialog"></div>');
				container.load('{link controller='Chat' action='Copyright'}{/link}', function() {
					$('body').append(container);
					WCF.showDialog('timsChatCopyrightDialog', { title: 'Tims Chat{if CHAT_SHOW_VERSION} {$chatVersion}{/if}' });
				});
			});
		})(jQuery, this)
	//]]>
</script>

{include file='footer' sandbox=false}
</body>
</html>