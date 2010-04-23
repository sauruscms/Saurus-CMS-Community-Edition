/**
	Initializes jQuery Lightbox preferences overrides
	Used by object_templates/gallery.html
*/

jQuery(function( $ ){
   
   $("div.GalleryThumbnail a.thumbnail").lightBox({ 
		imageBlank: wwwroot + '/extensions/saurus4/images/lightbox-blank.gif',
		imageLoading: wwwroot + '/extensions/saurus4/images/lightbox-loading.gif',
		imageBtnClose: wwwroot + '/extensions/saurus4/images/lightbox-close.gif',		
		imageBtnPrev: wwwroot + '/extensions/saurus4/images/lightbox-prev.gif',
		imageBtnNext: wwwroot + '/extensions/saurus4/images/lightbox-next.gif',
		overlayBgColor: '#fff',
		containerResizeSpeed: 5,
		txtImage: '',
		txtOf: '/'
	});
});