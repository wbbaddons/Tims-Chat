{hascontent}
	<header class="boxHeadline boxSubHeadline">
		<h1>{lang}chat.header.menu.chat{/lang}</h1>
	</header>
	
	<div class="container marginTop">
		<ul class="containerList">
			{content}
				{foreach from=$rooms item='room'}
					{assign var='users' value=$room->getUsers()}
					
					{if $users|count > 0}
						<li>
							<div>
								<div>
									<hgroup class="containerHeadline">
										<h1><a href="{link controller='Chat' object=$room}{/link}">{$room}</a> <span class="badge">{#$users|count}</span></h1>
										<h2>{$room->topic|language}</h2>
									</hgroup>
									
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
			{/content}
		</ul>
	</div>
{/hascontent}