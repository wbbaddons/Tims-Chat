<div
data-active-room-id="{if $activeRoomID|isset}{$activeRoomID}{else}0{/if}"
{if $boxID|isset} data-box-id="{$boxID}"
{else}
data-skip-empty-rooms="{if $skipEmptyRooms|isset && $skipEmptyRooms}1{else}0{/if}"
data-is-sidebar="0"
{/if}
>
	{capture assign='chatBoxRoomList'}
		{foreach from=$boxRoomList item='room'}
			{if $room->canSee() && (!$skipEmptyRooms|isset || !$skipEmptyRooms || !$room->getUsers()|empty)}
				<li{if $activeRoomID|isset && $room->roomID === $activeRoomID} class="active"{/if}>
					<div class="box48">
						<div>
							{assign var='disallowJoinReason' value=null}
							<div class="containerHeadline">
								{if $room->canJoin(null, $disallowJoinReason)}
									<h3>
										<a href="{link controller='Room' application='chat' object=$room}{/link}">{$room->getTitle()}</a>
										<span class="badge">{#$room->getUsers()|count}{if $room->userLimit} / {#$room->userLimit}{/if}</span>
									</h3>

									{if $room->getTopic()}
										<p class="chatRoomTopic">{@$room->getTopic()}</p>
									{/if}
								{else}
									<h3>{$room->getTitle()} <span class="badge">{#$room->getUsers()|count}{if $room->userLimit} / {#$room->userLimit}{/if}</span></h3>
								{/if}
							</div>

							{if !$room->getUsers()|empty || $disallowJoinReason !== null}
								<div class="containerContent">
									{if !$room->getUsers()|empty}
										<ul class="inlineList commaSeparated">
											{foreach from=$room->getUsers() item='user'}
												<li{if $user->chatAway} class="away"{/if}><a href="{link controller='User' object=$user}{/link}" class="userLink" data-user-id="{$user->userID}">{$user->username}</a></li>
											{/foreach}
										</ul>
									{/if}

									{if $disallowJoinReason !== null}
										<div class="error">{$disallowJoinReason->getMessage()}</div>
									{/if}
								</div>
							{/if}
						</div>
					</div>
				</li>
			{/if}
		{/foreach}
	{/capture}
	{if $chatBoxRoomList|trim}
		<ol class="chatBoxRoomList containerList" data-hash="{$chatBoxRoomList|trim|sha1}">
			{@$chatBoxRoomList}
		</ol>
	{else}
		<p class="info chatBoxRoomList">{lang}chat.box.noRooms{/lang}</p>
	{/if}

	<script>
		;(function (container) {
			document.addEventListener("DOMContentLoaded", function(event) {
				require([ 'Bastelstu.be/Chat/BoxRoomList' ], BoxRoomList => new BoxRoomList(container))
			})
		})(document.currentScript.parentNode);
	</script>
</div>
