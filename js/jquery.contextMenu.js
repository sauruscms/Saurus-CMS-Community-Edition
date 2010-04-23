/**
 * jQuery.contextMenu - Context menu from a button
 * Copyright (c) 2007-2009 Saurus
 * Licensed under LGPL.
 * @author saurus@saurus.info
 * @version 1.2
 *
 * http://www.saurus.info/context-button-plugin
 */
(function($)
{
	$.fn.contextMenu = function(buttons)
	{
		var argButtons = buttons;
		
		return this.each(function (i)
		{
			// give each dropdown unique index
			var index = ($(this).data('index') == undefined ? i + Math.round(Math.random() * 10000).toString() : $(this).data('index'));
			$(this).data('index', index);
			
			// merge buttons given in argument and buttons in anchor element 
			var buttons = ($(this).attr('buttons') == undefined ? [] : $(this).attr('buttons').split(','));
			
			for(var i in argButtons)
			{
				if($.inArray(argButtons[i], buttons) == -1) buttons.push(argButtons[i]);
			}
			
			if(!$.isArray(buttons)) buttons = [];
			
			// variables passed to action item
			var data = {
				anchor: $(this),
				buttons: buttons,
				index: index
			};
			
			// click event
			if($.fn.contextMenu.settings.menuOpenEvent == 'click')
			{
				$(this).click(function ()
				{
					$.fn.contextMenu.openMenu(data);
					
					return false;
				});
			}
			
			var delayTimer;
			
			// hover event
			$(this).hover(function ()
			{
				if($.fn.contextMenu.settings.menuOpenEvent == 'hover')
				{
					if(delayTimer)
					{
						clearTimeout(delayTimer);
						delayTimer = null;
					}
					
					delayTimer = setTimeout(function ()
					{
						$.fn.contextMenu.openMenu(data);
		
					}, $.fn.contextMenu.settings.delayHoverEvent);
				}
			},
			function()
			{
				if(delayTimer)
				{
					clearTimeout(delayTimer);
					delayTimer = null;
				}
			});
		});
	};
	
	$.fn.contextMenu.openMenu = function (data)
	{
		// hide all other buttons
		$('ul.context_plugin_dropdown').hide();
		
		var contextMenu = $('ul#context_plugin_dropdown_' + data.index);
		
		if(contextMenu.length == 0)
		{
			// create the context menu container element
			$('body:first').prepend('<ul id="context_plugin_dropdown_' + data.index + '" class="context_plugin_dropdown"></ul>');
			
			contextMenu = $('ul#context_plugin_dropdown_' + data.index);
			
			// add all action elements
			var actionItems = '';
			
			for(var i in data.buttons) if($.fn.contextMenu.actions[data.buttons[i]])
			{
				actionItems += '<li class="context_plugin_item"><a class="' + $.fn.contextMenu.actions[data.buttons[i]].name + '" href="javascript:void(0);">' + $.fn.contextMenu.actions[data.buttons[i]].title + '</a></li>';
			}
			
			contextMenu.html(actionItems);
			
			// attach data to actions
			contextMenu.children('li.context_plugin_item').children('a').data('objectData', data);
			
			// on mouse out, hide the menu
			contextMenu.hover(function () {}, function ()
			{
				$(this).hide();
			});
		}
		
		// position the context menu on top of the anchor
		contextMenu.css('top', Math.round(data.anchor.offset().top - data.anchor.scrollTop()) + 'px');
		contextMenu.css('left', Math.round(data.anchor.offset().left - data.anchor.scrollLeft()) + 'px');
		
		// if context menu would go behind the bottom edge
		if(data.anchor.offset().top + contextMenu.height() > $(document).height())
		{
			contextMenu.css('top', Math.round(data.anchor.offset().top - contextMenu.height() + data.anchor.height() - data.anchor.scrollTop()) + 'px');
		}
		
		// if context menu would go behind the right edge
		if(data.anchor.offset().left + contextMenu.width() > $(document).width())
		{
			contextMenu.css('left', Math.round(data.anchor.offset().left - contextMenu.width() + data.anchor.width() - data.anchor.scrollLeft()) + 'px');
		}
		
		contextMenu.show();
	}
		
	// actions array
	$.fn.contextMenu.actions = [];
	
	// method to add actions
	$.fn.contextMenu.addAction = function (action)
	{
		$.fn.contextMenu.actions[action.name] = action;
		
		// bind menu hiding
		$('a.' + action.name).live('click', function ()
		{
			$('ul.context_plugin_dropdown').hide();
		});
		
		// bind all future clicks
		$('a.' + action.name).live('click', action.bind);
	};
	
	$.fn.contextMenu.settings =
	{
		menuOpenEvent: 'click', // event that opens the menu click or hover, default hover
		delayHoverEvent: 333 // delays the menu opening event by X ms, default 333ms
	};
	
})(jQuery);

/**
 * jQuery.getAttributes - get all attributes of a DOM element, optionally filter with a RegExp
 * Copyright (c) 2007-2009 Saurus
 * Licensed under LGPL.
 *
 * @author saurus@saurus.ee
 * @version 1.0
 */
(function($)
{
	$.fn.getAttributes = function (filter)
	{
		var attrs = [];
		
		this.each(function ()
		{
			
			for(var i in this.attributes) if(this.attributes[i] && this.attributes[i].nodeName != undefined) 
			{
				if(filter == undefined)
				{
					attrs[this.attributes[i].nodeName] = this.attributes[i].value;
				}
				else if(this.attributes[i].nodeName.match(filter))
				{
					attrs[this.attributes[i].nodeName] = this.attributes[i].value;
				}
			}
		});
		
		return attrs;
	};
})(jQuery);
