<header class="boxHeadline boxSubHeadline">
	<hgroup>
		<h1>{lang}chat.header.menu.chat{/lang}</h1>
	</hgroup>
</header>

<div class="container marginTop">
	<ul>
	{foreach from=$rooms item='room'}
		<li><strong>{$room}</strong>: <ul class="dataList">{implode from=$room->getUsers() item='user'}<li><a href="{link controller='User' object=$user}{/link}" class="userLink" data-user-id="{$user->userID}">{$user}</a></li>{/implode}</ul>
	{/foreach}
	</ul>
</div>