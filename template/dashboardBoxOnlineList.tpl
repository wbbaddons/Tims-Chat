<header class="boxHeadline boxSubHeadline">
	<hgroup>
		<h1>{lang}chat.header.menu.chat{/lang}</h1>
	</hgroup>
</header>

<div class="container marginTop">
	<ul class="containerList">
		{foreach from=$rooms item='room'}
			{assign var='users' value=$room->getUsers()}
			
			{if $users|count > 0}
				<li>
					<div>
						<div>
							<hgroup class="containerHeadline">
								<h1><a href="{link controller='Chat' object=$room}{/link}">{$room}</a><small> - {$room->topic|language}</small></h1>
								<h2><strong>{#$users|count} Users</strong></h2>
							</hgroup>
							<ul class="dataList">
								{implode from=$room->getUsers() item='user'}<li><a href="{link controller='User' object=$user}{/link}" class="userLink" data-user-id="{$user->userID}">{$user}</a></li>{/implode}
							</ul>
						</div>
					</div>
				</li>
			{/if}
		{/foreach}
	</ul>
</div>