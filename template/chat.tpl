{include file='documentHeader'}

<head>
	<title>{$room} - {lang}wcf.chat.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
	<style type="text/css">
		@import url("{@$__wcf->getPath('wcf')}style/timwolla.wcf.chat.css");
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
		.timsChatMessage{$type|concat:'JOIN'|constant}, .timsChatMessage{$type|concat:'LEAVE'|constant} {
			background-position: left top;
			background-repeat: no-repeat;
		}
		
		.timsChatMessage{$type|concat:'JOIN'|constant} {
			background-image: url({icon size='S'}toRight1{/icon});
		}
		
		.timsChatMessage{$type|concat:'LEAVE'|constant} {
			background-image: url({icon size='S'}toLeft1{/icon});
		}
		
		.ajaxLoad {
			background-image: url({icon size='S'}spinner1{/icon});
		}

		/*
		 * jCounter CSS
		 * 
		 * TODO: Seperate jCounter completely from Tims-Chat
		 */
		.jsCounterContainer {
			display: table;
		}
		
		.jsCounterContainer > div {
			display: table-row;
		}
		
		.jsCounterInput {
			height: 16px;
		}
		
		.jsCounterInput, .jsCounter {
			display: table-cell;
		}
		
		.jsCounterInput, .jsCounterContainer {
			width: 100%;
		}
		
		.jsCounter {
			background-color: rgba(0, 0, 0, 0.7);
			padding: 0 5px 0 10px;
			position: relative;
			z-index: 0 !important;
			border-radius: 0px 5px 5px 0px;
			border: 1px solid rgba(255, 255, 255, 0.3);
			width: 30px;
		}
		
		.jsCounter.color-1 {
			color: #FFFFFF;
		}
		.jsCounter.color-2 {
			color: rgba(255,255,255,0.5);
		}
		.jsCounter.color-3 {
			color: #D40D12;
		}
	</style>
</head>

<body id="tpl{$templateName|ucfirst}">
{capture assign='sidebar'}
<div id="sidebarContent" class="sidebarContent">
	<nav class="timsChatSidebarTabs">
		<ul>
			<li id="toggleUsers" class="active"><a href="javascript:;" title="{lang}wcf.chat.users{/lang}">{lang}wcf.chat.users{/lang} <span class="badge">0</span></a></li>
			<li id="toggleRooms"><a href="javascript:;" title="{lang}wcf.chat.rooms{/lang}" data-refresh-url="{link controller="Chat" action="RefreshRoomList"}{/link}">{lang}wcf.chat.rooms{/lang} <span class="badge">{#$rooms|count}</span></a></li>
		</ul>
	</nav>
	
	<div id="sidebarContainer">
		<ul id="timsChatUserList">
		{*section name=user start=1 loop=26}
			<li class="timsChatUser">
				<a href="javascript:;">User {$user}</a>
				<ul class="timsChatUserMenu">
					<li>
						<a href="javascript:;">{lang}wcf.chat.query{/lang}</a>
						<a href="javascript:;">{lang}wcf.chat.kick{/lang}</a>
						<a href="javascript:;">{lang}wcf.chat.ban{/lang}</a>
						<a href="{link controller="User" id=$user}{/link}">{lang}wcf.chat.profile{/lang}</a>
					</li>
				</ul>
			</li>
		{/section*}
		</ul>
		<nav id="timsChatRoomList" class="sidebarMenu" style="display: none;">
			<div>
				<ul>
				{foreach from=$rooms item='roomListRoom'}
					{if $roomListRoom->canEnter()}
						<li{if $roomListRoom->roomID == $room->roomID} class="activeMenuItem"{/if}>
							<a href="{link controller='Chat' object=$roomListRoom}{/link}" class="timsChatRoom">{$roomListRoom}</a>
						</li>
					{/if}
				{/foreach}
				</ul>
				<div style="text-align: center;"><button type="button">Force Refresh</button></div>
			</div>
		</nav>
	</div>
</div>
{/capture}
{include file='header' sandbox=false sidebarOrientation='right'}

<div id="timsChatRoomContent">
	<div id="timsChatTopic" class="border"{if $room->topic|language === ''} style="display: none;"{/if}>{$room->topic|language}</div>
	<div class="timsChatMessageContainer border content">
		<ul>
			<noscript><li class="error">{lang}wcf.chat.noJs{/lang}</li></noscript>
		</ul>
	</div>
	
	<form id="timsChatForm" action="{link controller="Chat" action="Send"}{/link}" method="post">
		<input type="text" id="timsChatInput" class="inputText long jsCounterInput" name="text" autocomplete="off" maxlength="{CHAT_LENGTH}" disabled="disabled" required="required" placeholder="{lang}wcf.chat.submit.default{/lang}" />
	</form>
	
	<div id="timsChatControls">
		<div id="smileyList" class="border">
			<ul class="smilies">
				{foreach from=$smilies item='smiley'}
					<li>
						<img src="{$smiley->getURL()}" alt="{$smiley->smileyCode}" title="{$smiley->smileyCode}" class="smiley" />
					</li>
				{/foreach}
			</ul>
		</div>
		<div id="timsChatOptions" class="border">
			<div class="smallButtons">
				<ul>
					<li>
						<a id="timsChatAutoscroll" href="javascript:;" class="timsChatToggle balloonTooltip" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="1">
							<img alt="" src="{icon size='S'}enabled1{/icon}" /> <span>{lang}wcf.chat.scroll{/lang}</span>
						</a>
					</li>
					<li>
						<a id="timsChatNotify" href="javascript:;" class="timsChatToggle balloonTooltip" title="{lang}wcf.global.button.enable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="0">
							<img alt="" src="{icon size='S'}disabled1{/icon}" /> <span>{lang}wcf.chat.notify{/lang}</span>
						</a>
					</li>
					<li>
						<a id="timsChatSmilies" href="javascript:;" class="timsChatToggle balloonTooltip" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="1">
							<img alt="" src="{icon size='S'}enabled1{/icon}" /> <span>{lang}wcf.chat.smilies{/lang}</span>
						</a>
					</li>
					<li>
						<a id="timsChatClear" href="javascript:;" class="balloonTooltip" title="{lang}wcf.chat.clear.description{/lang}">
							<img alt="" src="{icon size='S'}delete1{/icon}" /> <span>{lang}wcf.chat.clear{/lang}</span>
						</a>
					</li>
					<li>
						<a id="timsChatMark" href="javascript:;" class="balloonTooltip" title="{lang}wcf.chat.mark.description{/lang}">
							<img alt="" src="{icon size='S'}check1{/icon}" /> <span>{lang}wcf.chat.mark{/lang}</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		{include file='chatCopyright'}
	</div>
</div>
{include file='chatJavascriptInclude'}
<script type="text/javascript">
	//<![CDATA[
		(function ($, window) {
			// populate templates
			TimWolla.WCF.Chat.titleTemplate = (new WCF.Template('{ldelim}$title} - {'wcf.chat.title'|language|encodeJS} - {PAGE_TITLE|language|encodeJS}')).compile();
			{capture assign='chatMessageTemplate'}{include file='chatMessage'}{/capture}
			TimWolla.WCF.Chat.messageTemplate = (new WCF.Template('{@$chatMessageTemplate|encodeJS}')).compile();
			
			// populate config
			TimWolla.WCF.Chat.config = {
				reloadTime: {@CHAT_RELOADTIME},
				animations: {@CHAT_ANIMATIONS},
				maxTextLength: {@CHAT_LENGTH}
			}
			WCF.Language.addObject({
				'wcf.chat.query': '{lang}wcf.chat.query{/lang}',
				'wcf.chat.kick': '{lang}wcf.chat.kick{/lang}',
				'wcf.chat.ban': '{lang}wcf.chat.ban{/lang}',
				'wcf.chat.profile': '{lang}wcf.chat.profile{/lang}',
				'wcf.chat.newMessages': '{lang}wcf.chat.newMessages{/lang}'
			});
			WCF.Icon.addObject({
				'timwolla.wcf.chat.chat': '{icon size='L'}chat1{/icon}'
			});
			{event name='shouldInit'}
			// Boot the chat
			TimWolla.WCF.Chat.init();
			{event name='didInit'}
			
			// show the last X messages
			TimWolla.WCF.Chat.handleMessages([
				{implode from=$newestMessages item='message'}
					{@$message->jsonify()}
				{/implode}
			]);
			
			// enable user-interface
			$('#timsChatInput').enable().jCounter().focus();
			$('#timsChatControls .copyright').click(function (event) {
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