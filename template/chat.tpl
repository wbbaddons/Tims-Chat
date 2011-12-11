{include file='documentHeader'}

<head>
	<title>{$room->getTitle()} - {lang}wcf.chat.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
	
	<style type="text/css">
		#chatBox {
			padding: 0;
		}
		
		#chatBox > div {
			text-align: center;
		}
		
		#chatBox aside, #chatRoomContent {
			text-align: left;
		}
		
		#chatRoomContent {
			margin: auto;
			padding: 1px 10px 10px;
			background-color: #FFFFFF;
			width: 68%;
			box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
			-moz-box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
			-webkit-box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
		}
		
		.chatSidebar.left {
			float: left;
			margin-left: -1px;
		}
	
		.chatSidebar.right {
			float: right;
			margin-right: -1px;
		}
		
		.chatSidebar {
			overflow: auto;
			padding: 0 1px 0 0;
			width: 190px;
		}
		
		#chatRoomContent:after {
			clear: both;
			height: 0;
			content: "";
			display: block;
		}		
		
		.chatSidebar h2 {
			background-image: url({icon}arrowDown{/icon});
			font-size: 130%;
			padding: 7px 25px 7px 35px;
			background-position: 15px center;
			background-repeat: no-repeat;
			background-size: 16px auto;
			color: #336699;
			cursor: default;
			font-weight: bold;
			margin-top: 5px;			
		}

		.chatSidebar ul li a {
			color: #6699CC;
			display: block;
			padding: 5px 25px 7px 35px;
			text-shadow: 0 1px 0 #FFFFFF;
		}
		
		.chatSidebar ul li.activeMenuItem a {
			color: #336699;
			font-weight: bold;
		}
		
		.chatSidebar.left ul li.activeMenuItem, .chatSidebar.right ul li.activeMenuItem .bgFix {
			background-color: #FFFFFF;
			box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
			margin-right: -1px;
			overflow: hidden;
		}
		
		#topic, #smileyList, #chatOptions {
			padding: 5px;
		}	
		
		.chatMessageConainer {
			min-height: 200px;
			overflow-y: scroll;
		}
		
		#smileyList .smilies li, .smallButtons li {
			display: inline;
			margin-right: 5px;
			margin-top: 5px;
		}
		
		#chatForm {
			margin-top: 15px;
			white-space: nowrap;
			margin-right: 34px;
			margin-top: 10px;
			height: 28px;
		}
		
		#chatForm .inputImage {
			height: 100%;
			margin-bottom: -8px;
		}
		
		#chatOptions {
			display: inline-block;
		}

		#chatUserList > ul > li > .bgFix a {
			background-image: url("wcf/icon/arrowRight.svg");
			background-position: 15px center;
			background-repeat: no-repeat;
			background-size: 16px auto;
		}
		
		#chatUserList > ul > li.activeMenuItem > .bgFix a {
			background-image: url("wcf/icon/arrowDown.svg");
		}
		
		.chatSidebar .chatUserMenu li a {
			margin-left: 30px !important;
		}
		
		.chatUserMenu {
			display: none;
		}
		
		#chatUserList > ul li a {
			margin-left: 20px;
		}
		
		.chatMessage time, .chatMessage time::before, .chatMessage time::after {
			font-size: .8em;
		}
		.chatMessage time::before {
			content: "[";
		}
		
		.chatMessage time::after {
			content: "]";
		}
		
		.chatSidebar a {
			outline: none;
		}
		
		.ajaxLoad {
			background-position: right center;
			background-repeat: no-repeat;
			background-image: url({icon}spinner1{/icon});
		}
		
		.bgFix {
			display: block;
		}
	</style>
</head>

<body id="tpl{$templateName|ucfirst}">
{include file='header' sandbox=false}

<header class="mainHeading">
	<img src="{icon size='L'}chat1{/icon}" alt="" />
	<hgroup>
		<h1>{lang}wcf.chat.title{/lang}</h1>
	</hgroup>
</header>

<div class="tabMenuContainer" data-active="chatBox" data-store="activeTabMenuItem">
	<nav class="tabMenu">
		<ul>
			<li class="ui-state-active"><a href="{link controller="Chat"}{/link}" title="{lang}wcf.chat.title{/lang}">{lang}wcf.chat.title{/lang}</a></li>
			<li><a href="{link controller="Chat" action="Log"}{/link}" title="{lang}wcf.chat.protocol{/lang}">{lang}wcf.chat.protocol{/lang}</a></li>
		</ul>
	</nav>
	
	<section id="chatBox" class="border tabMenuContent hidden" data-menu-item="chatBox">
		<div>
			<aside id="chatRoomList" class="chatSidebar left">
				<div>
					<h2>{lang}wcf.chat.rooms{/lang}</h2>
					<ul>
					{foreach from=$rooms item='roomListRoom'}
						<li{if $roomListRoom->roomID == $room->roomID} class="activeMenuItem"{/if}>
							<a id="room-{$roomListRoom->roomID}" href="{link controller='Chat' object=$roomListRoom}{/link}" class="chatRoom">{$roomListRoom->getTitle()}</a>
						</li>
					{/foreach}
					</ul>
				</div>
			</aside>
			<aside id="chatUserList" class="chatSidebar right">
				<h2>{lang}wcf.chat.users{/lang}</h2>
				<ul>
				{section name=user start=1 loop=11}
					<li id="user-{$user}" class="chatUser">
						<span class="bgFix"><a class="chatUserLink" href="javascript:void(0)">User {$user}</a></span>
						<ul class="chatUserMenu">
							<li>
								<a href="javascript:void(0)">Query</a>
								<a href="javascript:void(0)">Kick</a>
								<a href="javascript:void(0)">Ban</a>
								<a href="{link controller="User" id=$user}{/link}">Profil</a>
							</li>
						</ul>
					</li>
				{/section}
				</ul>
			</aside>
			<div id="chatRoomContent">
				<div id="topic" class="border">Dieser Raum ist zum Testen da.</div>
				<div class="chatMessageConainer border content">
					<ul></ul>
				</div>
				
				<form id="chatForm" action="{link controller="Chat" action="Send"}{/link}" method="post">
					<input type="text" id="chatInput" class="inputText" style="width: 100%" name="text" autocomplete="off" />
					<input type="image" class="inputImage" alt="Absenden" src="{icon size=M}toRight1{/icon}" />
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
									<a id="chatAutoscrollButton" href="javascript: void(0);">
										<img alt="" src="{icon}enabled1{/icon}"> <span>Scrollen</span>
									</a>
								</li>
								<li>
									<a href="javascript: void(0);">
										<img alt="" src="{icon}disabled1{/icon}"> <span>Benachrichtigen</span>
									</a>
								</li>
								<li>
									<a href="javascript: void(0)">
										<img alt="" src="{icon}delete1{/icon}"> <span>Chat leeren</span>
									</a>
								</li>
								<li>
									<a href="javascript: void(0)">
										<img alt="" src="{icon}check1{/icon}"> <span>Markieren</span>
									</a>
								</li>											
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<script type="text/javascript">
	//<![CDATA[
		TimWolla.WCF.Chat.titleTemplate = new WCF.Template('{ldelim}$title} - {'wcf.chat.title'|language|encodeJS} - {PAGE_TITLE|language|encodeJS}');
		{capture assign='chatMessageTemplate'}{include file='chatMessage'}{/capture}
		TimWolla.WCF.Chat.messageTemplate = new WCF.Template('{@$chatMessageTemplate|encodeJS}');
		TimWolla.WCF.Chat.init({$room->roomID}, 1);
		TimWolla.WCF.Chat.handleMessages([
			{implode from=$newestMessages item='message'}
				{@$message->jsonify()}
			{/implode}
		]);
	//]]>
</script>

{include file='footer' sandbox=false}
</body>
</html>