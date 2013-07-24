<div id="timsChatLog">
	<div class="timsChatMessageContainer">
		<ul>
		</ul>
	</div>
</div>
<script>
	//<![CDATA[
		var log = new be.bastelstu.Chat.Log(chat);
		log.handleMessages([
			{implode from=$messages item='message'}
				{@$message->jsonify()}
			{/implode}
		]);
	//]]>
</script>
