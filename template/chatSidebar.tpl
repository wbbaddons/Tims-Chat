<div id="sidebarContent" class="wcf-sidebarContent">
	<nav class="timsChatSidebarTabs">
		<ul>
			<li id="toggleUsers" class="active"><a href="javascript:;" title="{lang}wcf.chat.users{/lang}">{lang}wcf.chat.users{/lang} <span class="wcf-badge">0</span></a></li>
			<li id="toggleRooms"><a href="javascript:;" title="{lang}wcf.chat.rooms{/lang}" data-refresh-url="{link controller="Chat" action="RefreshRoomList"}{/link}">{lang}wcf.chat.rooms{/lang} <span class="wcf-badge">{#$rooms|count}</span></a></li>
		</ul>
	</nav>
	
	<div id="sidebarContainer">
		<ul id="timsChatUserList">
		</ul>
		<nav id="timsChatRoomList" class="wcf-sidebarMenu" style="display: none;">
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
				<div style="text-align: center;"><button type="button">{lang}wcf.chat.forceRefresh{/lang}</button></div>
			</div>
		</nav>
	</div>
</div>