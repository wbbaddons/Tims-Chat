###
# jCounter - a simple character counter
#
# @author	Maximilian Mader
# @copyright	2011 Maximilian Mader
# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
# @package	jQuery.jCounter
###
(($) ->
	$.fn.jCounter = (container, options) ->
		options = $.extend
			max: 0
			counterClass: 'jCounter'
			countUp: false
			cssFile: 'wcf/style/jCounter.css'
			width: '100%'
		, options
		
		if this.attr('maxlength')
			max = this.attr('maxlength')
		else max = options.max
		
		if $('#jCounterCSS').length == 0
			$('head').append('<link id="jCounterCSS" rel="stylesheet" type="text/css" href="' + options.cssFile + '" >')

		if !container
			if !this.hasClass('jCounterInput')
				this.addClass('jCounterInput')
			this.wrap('<div class="jCounterContainer" style="width: ' + options.width + '"><div></div></div>').parent().append('<div class="' + options.counterClass + ' color-1">' + max + '</div>');
			jCounterContainer = $(this).parent().children('.' + options.counterClass)
		else
			if typeof container is 'object'
				jCounterContainer = container
			else
				jCounterContainer = $ container
		
		this.on 'keypress keyup', $.proxy () ->
			if options.countUp
				length = this.val().length
			else
				length = max - this.val().length
			
			if options.countUp && max > 0
				if length < max / 2
					color = 1
				else if length >= max / 2 and length <= max / 1.2
					color = 2
				else
					color = 3
			else if options.countUp
				color = 1
			else
				if length > max / 2
					color = 1
				else if length <= max / 2 and length >= max / 6
					color = 2
				else
					color = 3
			
			jCounterContainer.text(length).attr('class', '').addClass(options.counterClass + ' color-'+color)
		, this
)(jQuery)