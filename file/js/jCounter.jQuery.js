(function($){
	$.fn.jCounter = function(jCounterID, options) {
		var jCounter = $(jCounterID);
		var defaultClass = jCounter.attr('class');
		maxChars = (options != null) ? options : 140;
		this.on('keypress keydown keyup', $.proxy(function() {
			var length = maxChars - this.val().length;
			if(length <= maxChars) color = 1;
			if(length <= maxChars / 2) color = 2;
			if(length <= maxChars / 7) color = 3;
			jCounter.text(length).addClass(defaultClass + ' color-'+color);
		}, this));
	}
})(jQuery);