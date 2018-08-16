{literal}
{if $message.payload.suspensions && $__window.Object.keys($message.payload.suspensions).length > 0}
	<li>
		<div class="containerHeadline">
			<h3>{lang}chat.messageType.be.bastelstu.chat.messageType.info.suspensions{/lang}</h3>
		</div>
		<div class="containerContent">
			<div class="messageTableOverflow">
				<table class="table">
					<thead>
						<tr>
							<th class="columnText columnType">{lang}chat.suspension.type{/lang}</th>
							<th class="columnText columnJudge">{lang}chat.suspension.judge{/lang}</th>
							<th class="columnText columnRoom">{lang}chat.suspension.room{/lang}</th>
							<th class="columnText columnTime">{lang}chat.acp.suspension.time{/lang}</th>
							<th class="columnText columnExpires">{lang}chat.acp.suspension.expires{/lang}</th>
							{event name='columnHeads'}
						</tr>
					</thead>

					<tbody>
						{foreach from=$message.payload.suspensions item="suspension"}
							<tr>
								<td class="columnText columnType">{lang}chat.suspension.type.{$suspension.objectType}{/lang}</td>
								<td class="columnText columnJudge">{$suspension.judge}</td>
								<td class="columnText columnRoom">{if $suspension.roomID !== null}<a href="{$suspension.room.link}">{$suspension.room.title}</a>{else}–{/if}</td>
								<td class="columnText columnTime">{@$suspension.timeElement}</td>
								<td class="columnText columnExpires">{if $suspension.expires !== null}{@$suspension.expiresElement}{else}{lang}chat.acp.suspension.expires.forever{/lang}{/if}</td>
								{event name='columns'}
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</li>
{/if}
{/literal}
