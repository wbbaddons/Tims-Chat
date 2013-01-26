<div id="timsChatLog">
	<div class="timsChatMessageContainer">
		<ul>
		</ul>
	</div>
</div>
<script type="text/javascript">
	//<![CDATA[
		var log = new be.bastelstu.WCF.Chat.Log(chat);
		log.handleMessages([
			{implode from=$messages item='message'}
				{@$message->jsonify()}
			{/implode}
		]);
	//]]>
</script>
