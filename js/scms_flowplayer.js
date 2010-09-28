/**
 * Initializes Flowplayer
 */
jQuery(function ()
{ 
	jQuery('.scms-flowplayer-anchor').each(function(index) {
		jQuery(this).attr('id', 'flownumber'+index);
		jQuery(this).html('');
		flowplayer(jQuery(this).attr('id'), {src: wwwroot+'/js/flowplayer/flowplayer.swf', wmode: 'transparent'}, {
			clip: {
				'scaling':'fit',
				'autoPlay': false,
				'autoBuffering': true,
				'onMetaData': function(clip) {
					jQuery('#flownumber'+index).css('width', clip.metaData.width);
					jQuery('#flownumber'+index).css('height', clip.metaData.height);
					jQuery('#flownumber'+index+' object').css('width', clip.metaData.width);
					jQuery('#flownumber'+index+' object').css('height', clip.metaData.height);
					//Hack for FF not to ask to open the file
					jQuery('#flownumber'+index).removeAttr('href');
				}
			}
		});
	});
}); 