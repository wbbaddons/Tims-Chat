{foreach from=$rooms item='room'}
	{assign var='users' value=$room->getUsers()}
	
	{if $showEmptyRooms || $users|count > 0}
		<li>
			<div>
				<div>
					<div class="containerHeadline">
						<h3><a href="{link application='chat' controller='Chat' object=$room}{/link}">{$room}</a> <span class="badge">{#$users|count}</span></h3>
						<p>{$room->topic|language}</p>
					</div>
					
					<ul class="dataList">
						{foreach from=$users item='user'}
							<li><a href="{link controller='User' object=$user}{/link}" class="userLink" data-user-id="{$user->userID}">{$user}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
		</li>
	{/if}
{/foreach}
