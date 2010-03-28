(function($){
	
	
$.fn.filelibrary = function(options){
	
	settings = $.extend({
		baseURL: '/',
		ajaxFileInfoURLprefix: '/admin/uploaded_files/info/',
		inPopup:true,
		type:'all',
		fileType:'page_link'
	},options);
	
	// We use each() as it's conventional for plugins but really we only expect one filebrowser per page
	
	return $(this).each(function(){
		
		var self = $(this);

		var selectedFileID = false;
		var selectedHyperlink = false;
		var selectedPageName = false;

		var infoMessage = $(self).find('.filebrowser_info_message').get(0);
		var sizeChooser = $(self).find('.filebrowser_size_chooser').get(0);
		var insertButton = $(self).find('.filebrowser_insert_button').get(0);
		var insertOptions = $(self).find('.filebrowser_image_insert_options').get(0);
		
	 	var thumbnails = $(self).find('.filebrowser_item');
		var pageLinks = $(self).find('.filebrowser_page_list li');
	
		if(settings.inPopup) {
			$('.filebrowser_image_insert_options').hide();
			$(insertButton).click(function(){
				insert();
			});
		}

		$(thumbnails).each(function(){
			$(this).click(function(){
				selectedFileID = $(this).attr('id').split('_')[2];
				$('.filebrowser_item').each(function(){$(this).removeClass('filebrowser_item_selected')});
				$(this).addClass('filebrowser_item_selected');
				$(infoMessage).html('loading...');
				selectFile(selectedFileID);
				return false;
			})
		});
		
		$(pageLinks).each(function(){
			$(this).click(function(){
				var href = $(this).find('a').attr('href');
				selectedHyperlink = href;
				selectedPageName = $(this).find('a').text();
				pageLinks.each(function(){$(this).removeClass('filebrowser_page_selected')});
				$(this).addClass('filebrowser_page_selected');
				$(infoMessage).html('<h3>'+selectedPageName+'</h3><p>'+selectedHyperlink+'</p>');
				selectPage(href);
				return false;
			});
		});
		
		// private methods that need to have the class variables in scope
		
		function insert() {
			if(settings.type == 'image') {
				var file_path = $('#filebrowser_selected_file_path').val();
				var file_url = file_path.split('/').join('|');
				file_url = 'file_library/file_library_files/thumb/src:'+file_url;
				var size = sizeChooser.value;
				if(size.length == 0) {
					alert('Please choose a size!');
				}
				else {
					FileBrowserDialogue.sendURLBack(settings.baseURL+file_url+'/size:'+size);
				}
			}
			else if(settings.fileType == 'file_download') {
				FileBrowserDialogue.sendURLBack(settings.baseURL+'file_library/file_library_files/download/'+selectedFileID);
			}
			else {
				FileBrowserDialogue.sendURLBack(selectedHyperlink);
			}
		}
		
		function selectFile(id) {
			$('.filebrowser_image_insert_options').hide();
			$(infoMessage).load(settings.ajaxFileInfoURLprefix+id, function(){
				$('.filebrowser_image_insert_options').show();
			});
		}
		
		function selectPage(href) {
			$('.filebrowser_image_insert_options').show();
		}
		
	});
}
		
})(jQuery);
