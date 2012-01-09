{include file='documentHeader'}

<head>
	<title>{$room} - {lang}wcf.chat.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
	<style type="text/css">
		@import url("{@RELATIVE_WCF_DIR}style/timwolla.wcf.chat.css");
		#chatUserList > li > .bgFix a {
			background-image: url({icon size='S'}arrowRight{/icon});
		}
		
		#chatUserList > li.activeMenuItem > .bgFix a {
			background-image: url({icon size='S'}arrowDown{/icon});
		}
		
		{assign var='type' value='\wcf\data\chat\message\ChatMessage::TYPE_'}
		.chatMessage{$type|concat:'JOIN'|constant}, .chatMessage{$type|concat:'LEAVE'|constant} {
			background-position: left top;
			background-repeat: no-repeat;
		}
		
		.chatMessage{$type|concat:'JOIN'|constant} {
			background-image: url({icon size='S'}toRight1{/icon});
		}
		
		.chatMessage{$type|concat:'LEAVE'|constant} {
			background-image: url({icon size='S'}toLeft1{/icon});
		}
		
		.ajaxLoad {
			background-image: url({icon size='S'}spinner1{/icon});
		}
		
		.counterContainer {
			display: table;
		}
		
		.counterContainer > div {
			display: table-row;
		}
		
		.counterInput {
			height: 16px;
		}
		
		.counterInput, .counter {
			display: table-cell;
		}
		
		.counterInput, .counterContainer {
			width: 100%;
		}
		
		.counter {
			background-color: rgba(0, 0, 0, 0.7);
			padding: 0 5px 0 10px;
			position: relative;
			z-index: 0 !important;
			border-radius: 0px 5px 5px 0px;
			border: 1px solid rgba(255, 255, 255, 0.3);
			width: 30px;
		}
		
		.counter.color-1 {
			color: #FFFFFF;
		}
		.counter.color-2 {
			color: rgba(255,255,255,0.5);
		}
		.counter.color-3 {
			color: #D40D12;
		}
	</style>
</head>

<body id="tpl{$templateName|ucfirst}">
{capture assign='sidebar'}
<div id="sidebarContent">
	<nav class="chatSidebarTabs">
		<ul>
			<li id="toggleUsers" class="active"><a href="javascript:;" title="{lang}wcf.chat.users{/lang}">{lang}wcf.chat.users{/lang} <span class="badge">25</span></a></li>
			<li id="toggleRooms"><a href="javascript:;" title="{lang}wcf.chat.rooms{/lang}" data-refresh-url="{link controller="Chat" action="RefreshRoomList"}{/link}">{lang}wcf.chat.rooms{/lang} <span class="badge">{#$rooms|count}</span></a></li>
		</ul>
	</nav>
	
	<div id="sidebarContainer">
		<ul id="chatUserList">
		{section name=user start=1 loop=26}
			<li id="user-{$user}" class="chatUser">
				<span class="bgFix"><a class="chatUserLink" href="javascript:;">User {$user}</a></span>
				<ul class="chatUserMenu">
					<li>
						<a href="javascript:;">{lang}wcf.chat.query{/lang}</a>
						<a href="javascript:;">{lang}wcf.chat.kick{/lang}</a>
						<a href="javascript:;">{lang}wcf.chat.ban{/lang}</a>
						<a href="{link controller="User" id=$user}{/link}">{lang}wcf.chat.profile{/lang}</a>
					</li>
				</ul>
			</li>
		{/section}
		</ul>
		<nav id="chatRoomList" class="sidebarMenu" style="display: none;">
			<div>
				<ul>
				{foreach from=$rooms item='roomListRoom'}
					{if $roomListRoom->canEnter()}
						<li{if $roomListRoom->roomID == $room->roomID} class="activeMenuItem"{/if}>
							<a href="{link controller='Chat' object=$roomListRoom}{/link}" class="chatRoom">{$roomListRoom}</a>
						</li>
					{/if}
				{/foreach}
				</ul>
			</div>
		</nav>
	</div>
</div>
{/capture}
{include file='header' sandbox=false sidebarOrientation='right'}

<div id="chatRoomContent">
	<div id="topic" class="border"{if $room->topic|language === ''} style="display: none;"{/if}>{$room->topic|language}</div>
	<div class="chatMessageContainer border content">
		<ul></ul>
	</div>
	
	<form id="chatForm" action="{link controller="Chat" action="Send"}{/link}" method="post">
		<input type="text" id="chatInput" class="inputText long counterInput" name="text" autocomplete="off" maxlength="{CHAT_LENGTH}" required="required" placeholder="{lang}wcf.chat.submit.default{/lang}" />
	</form>
	
	<div id="chatControls">
		<div id="smileyList" class="border">
			<ul class="smilies">
				{foreach from=$smilies item='smiley'}
					<li>
						<img src="{$smiley->getURL()}" alt="{$smiley->smileyCode}" title="{$smiley->smileyCode}" class="smiley" />
					</li>
				{/foreach}
			</ul>
		</div>
		<div id="chatOptions" class="border">
			<div class="smallButtons">
				<ul>
					<li>
						<a id="chatAutoscroll" href="javascript:;" class="chatToggle balloonTooltip" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="1">
							<img alt="" src="{icon}enabled1{/icon}" /> <span>{lang}wcf.chat.scroll{/lang}</span>
						</a>
					</li>
					<li>
						<a id="chatNotify" href="javascript:;" class="chatToggle balloonTooltip" title="{lang}wcf.global.button.enable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="0">
							<img alt="" src="{icon}disabled1{/icon}" /> <span>{lang}wcf.chat.notify{/lang}</span>
						</a>
					</li>
					<li>
						<a id="chatSmilies" href="javascript:;" class="chatToggle balloonTooltip" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="1">
							<img alt="" src="{icon}enabled1{/icon}" /> <span>{lang}wcf.chat.smilies{/lang}</span>
						</a>
					</li>
					<li>
						<a id="chatClear" href="javascript:;" class="balloonTooltip" title="Clear the chat">
							<img alt="" src="{icon}delete1{/icon}" /> <span>{lang}wcf.chat.clear{/lang}</span>
						</a>
					</li>
					<li>
						<a id="chatMark" href="javascript:;" class="balloonTooltip" title="Show checkboxes">
							<img alt="" src="{icon}check1{/icon}" /> <span>{lang}wcf.chat.mark{/lang}</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	//<![CDATA[
		TimWolla.WCF.Chat.titleTemplate = new WCF.Template('{ldelim}$title} - {'wcf.chat.title'|language|encodeJS} - {PAGE_TITLE|language|encodeJS}');
		{capture assign='chatMessageTemplate'}{include file='chatMessage'}{/capture}
		TimWolla.WCF.Chat.messageTemplate = new WCF.Template('{@$chatMessageTemplate|encodeJS}');
		TimWolla.WCF.Chat.config = { 
			reloadTime: {CHAT_RELOADTIME},
			animations: {CHAT_ANIMATIONS},
			maxTextLength: {CHAT_LENGTH}
		}
		{event name='shouldInit'}
		TimWolla.WCF.Chat.init();
		{event name='didInit'}
		TimWolla.WCF.Chat.handleMessages([
			{implode from=$newestMessages item='message'}
				{@$message->jsonify()}
			{/implode}
		]);

		$('#chatInput').jCounter();
	//]]>
</script>

{include file='footer' sandbox=false}
</body>
</html>