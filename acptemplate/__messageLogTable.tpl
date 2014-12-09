{if $messages|count > 0}
	<table class="table">
		<thead>
			<tr>
				<th>{lang}wcf.global.objectID{/lang}</th>
				<th>{lang}chat.global.time{/lang}</th>
				<th colspan="2">{lang}wcf.user.username{/lang}</th>
				<th>{lang}chat.acp.log.message{/lang}</th>
			</tr>
		</thead>
		
		<tbody>
			{foreach from=$messages item="message"}
				<tr>
					<td class="columnID">{$message->messageID}</td>
					<td style="width: 1px !important;">{$message->time|date:"chat.global.timeFormat"}</td>
					<td class="columnIcon"><p class="framed">{@$message->getUserProfile()->getAvatar()->getImageTag(24)}</p></td>
					<td class="columnTitle columnUsername right" style="width: 1px !important;">{$message->username}</td>
					<td>{@$message->getFormattedMessage("text/simplified-html")}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}
