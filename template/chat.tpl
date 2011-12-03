{include file='documentHeader'}

<head>
	<title>{$room->title|language} - {lang}wcf.chat.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
	
	<style type="text/css">
		#chatbox {
			
		}
		
		.table {
			display: table;
			table-layout: fixed;
			width: 100%;
		}
		
		.table > div {
			display: table-row;
		}
		
		.first {
			width: 10%;
			background-color: #D8E7F5;
			box-shadow: 0 0 5px rgba(0, 0, 0, 0.1) inset;
		}
		
		.second {
			width: 80%;
		}
		
		.third {
			width: 10%;
			background-color: #D8E7F5;
			box-shadow: 0 0 5px rgba(0, 0, 0, 0.1) inset;
		}
		
		.column {
			display: table-cell;
			margin: 0;
			padding: 0;
		}
		
		.second.column > div {
			padding: 15px 25px;
			padding: 15px 25px;
		}
		
		#smileyList {
			padding: 5px;
		}

		.smilies li {
			display: inline;
			margin-right: 5px;
			margin-top: 5px;
		}
		
		/*
		.tabMenu {
			padding: 0 15px;
			text-align: left;
		}
		*/
		
		.chatMessage {
			min-height: 200px;
		}
		
		/*
		.chatSidebar {
		    float: right;
			width: 100%;
		}
		*/
		
		/*
		.chatSidebar > div h1.activeMenuItem {
			background-image: url("wcf/icon/arrowDown.svg");
		}
		*/
		
		.chatSidebar > div h1 {
			font-size: 130%;
			padding: 7px 25px 7px 35px;
		}
		
		.chatSidebar > div h1 {
			background-image: url("wcf/icon/arrowRight.svg");
			background-position: 15px center;
			background-repeat: no-repeat;
			background-size: 16px auto;
			color: #336699;
			cursor: pointer;
			font-weight: bold;
			margin-top: 5px;
			position: relative;
		}

		.chatSidebar #chatUserList .sidebarMenuGroup > ul > li > a {
			background-image: url("wcf/icon/arrowRight.svg");
			background-position: 15px center;
			background-repeat: no-repeat;
			background-size: 16px auto;
		}
		
		.chatSidebar #chatUserList .sidebarMenuGroup > ul > li.activeMenuItem > a {
			background-image: url("wcf/icon/arrowDown.svg");
		}
		
		.chatSidebar > div ul li a {
			color: #6699CC;
			display: block;
			padding: 5px 25px 7px 35px;
			text-shadow: 0 1px 0 #FFFFFF;
		}
		
		.chatSidebar > div ul li.activeMenuItem {
			background-color: #FFFFFF;
			box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
			overflow: hidden;
			z-index: 110;
			font-size: 110%;
		}
		
		.chatSidebar > div ul li.activeMenuItem a {
			color: #336699;
			font-weight: bold;
			display: block;
			padding: 5px 25px 7px 35px;
			text-shadow: 0 1px 0 #FFFFFF;
		}
		
		.chatSidebar.right > div ul li.activeMenuItem {
			margin-right: -1px;
		}
		
		.chatSidebar.left > div ul li.activeMenuItem {
			margin-left: -1px;
		}
		
		.chatSidebar .selectedUser {
			margin-left: 20px;
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

<nav class="tabMenu">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"><a title="{lang}wcf.chat.title{/lang}" href="{link controller="Chat"}{/link}">{lang}wcf.chat.title{/lang}</a></li>
		<li class="ui-state-default ui-corner-top ui-tabs-selected"><a title="Log" href="{link controller="Chat" isRaw="true"}Log{/link}">Protokoll</a></li>
	</ul>
</nav>
<div id="chatbox" class="border tabMenuContent ui-tabs-panel ui-widget-content ui-corner-bottom">
	<div class="table border">
		<div>		
			<div class="first column">
				<div class="chatSidebar left">
					<div id="chatChannelList">
						<h1 data-menu-item="timwolla.wcf.chat.channellist" class="menuHeader activeMenuItem">Channel</h1>
						<div class="sidebarMenuGroup">
							<ul>
							{foreach from=$rooms item='roomListRoom'}
								<li{if $roomListRoom->roomID == $room->roomID} class="activeMenuItem"{/if}>
									<a href="{link controller='Chat' object=$roomListRoom}{/link}">{$roomListRoom->title|language}</a>
								</li>
							{/foreach}
							</ul>
						</div>
					</div>
				</div>
			</div>			
			<div class="second column">
				<div>
					<div class="chatMessage border content">
						[HH:MM:SS] &lt;User 1&gt; Test
					</div>
					<form style="margin-top: 10px;" id="chatForm" action="index.php?form=Chat" method="post">
						<div class="table">
							<div>
								<div class="column" style="width: 95%;">
									<input type="text" id="chatInput" class="inputText" style="width: 100%" name="text" autocomplete="off">
								</div>
								<div class="column" style="width: 5%; text-align: center;">
									<input type="image" class="inputImage" alt="Absenden" src="wcf/icon/toRight1.svg" style="width: 24px; margin-left: 5px; vertical-align: sub;">
								</div>
							</div>
						</div>
					</form>
					<div id="smileyList" class="border">
						<ul class="smilies">
							{foreach from=$smilies item='smiley'}
								<li>
									<img src="{$smiley->getURL()}" alt="{$smiley->smileyCode}" title="{$smiley->smileyCode}" class="smiley" onclick="TimWolla.WCF.Chat.insertSmiley('{$smiley->smileyCode}');" />
								</li>
							{/foreach}
						</ul>
					</div>
				</div>
			</div>			
			<div class="third column">
				<div class="chatSidebar right">
					<div id="chatUserList">
						<h1 data-menu-item="timwolla.wcf.chat.userlist" class="menuHeader activeMenuItem">User</h1>
						<div class="sidebarMenuGroup">
							<ul>
								<li class="activeMenuItem">
									<a href="javascript:void(0)">User 1</a>
									<ul class="selectedUser">
										<li>
											<a href="javascript:void(0)">Query</a>
											<a href="javascript:void(0)">Kick</a>
											<a href="javascript:void(0)">Ban</a>
											<a href="javascript:void(0)">Profil</a>
										</li>
									</ul>
								</li>
								<li>
									<a href="javascript:void(0)">User 2</a>
								</li>
								<li>
									<a href="javascript:void(0)">User 3</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>			
		</div>
	</div>
</div>

<script type="text/javascript">
	//<![CDATA[
		TimWolla.WCF.Chat.init({$room->roomID}, 1);
	//]]>
</script>

{include file='footer' sandbox=false}
</body>
</html>