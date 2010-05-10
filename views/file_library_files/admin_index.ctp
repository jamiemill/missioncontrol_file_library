<?php

// type can be image, file or file_download
// file is for internal page links

$linkSuffix = '';
$recordHeaderSettings = array();
if($inPopup) {
	$linkSuffix .= 'context:popup/';
	$recordHeaderSettings = array('add'=>false);
}
$linkSuffix .= 'type:'.$type.'/';
if($type == 'file') {
	$linkSuffix .= 'file_type:'.$file_type.'/';
}

?>

<?php echo $this->element('record_navigation', array('plugin'=>'core','recordHeaderSettings'=>$recordHeaderSettings))?>
<?php 
$html->addCrumb('File Library');
?>
<?php echo $this->element('crumb_heading',array('plugin'=>'core'))?>

<div id="filebrowser" class="main box">

	<div class="box_head">
		<?php if($inPopup && $type == 'file' && $file_type == 'file_download') : ?>
			<h2><?php __('All files') ?></h2>
			<?php echo $html->link('switch to internal links >','/admin/file_library/file_library_files/index/file_type:page_link/context:popup/',array('class'=>'button'))?>
		<?php elseif($inPopup && $type == 'file' && $file_type == 'page_link') : ?>
			<h2><?php __('All internal links') ?></h2>
			<?php echo $html->link('switch to file library >','/admin/file_library/file_library_files/index/file_type:file_download/context:popup/',array('class'=>'button'))?>
		<?php else : ?>
			<h2><?php __('All files') ?></h2>
		<?php endif ?>
	</div>
	
	<div class="box_content">

		<div class="sidebar">
				
			<?php // FILE BROWSER INFO AREA ////////// ?>
		
			<div class="filebrowser_info_area">
			
				<div class="filebrowser_info_message">Please select a file.</div>

				<div class="filebrowser_image_insert_options">

					<?php if($inPopup) : ?>
	
						<?php if($type=='image') : ?>
		
							<select class="filebrowser_size_chooser">
								<option value="">-- select a size --</option>
			
								<?php foreach($thumbSizes as $key=>$val) : ?>
									<option value="<?php echo $key ?>"><?php echo $key ?> (<?php echo $val[0] ?> x <?php echo $val[1] ?> pixels)</option>
								<?php endforeach ?>

							</select>
	
							<input type="button" class="filebrowser_insert_button" value="insert" />
		
						<?php elseif($type == 'file' && $file_type == 'page_link') : ?>
							<input type="button" class="filebrowser_insert_button" value="insert link" />				
						<?php elseif($type == 'file' && $file_type == 'file_download') : ?>
							<input type="button" class="filebrowser_insert_button" value="insert download link" />
						<?php endif ?>

					<?php endif ?>

				</div>
			
			</div>
		
			<?php // END FILE BROWSER INFO AREA ////////// ?>
		
		</div>

		<div class="innerEditArea">
				
			<?php if(($type == 'file' && $file_type == 'file_download') || $type == 'image') : ?>
		
			<?php ///// FILE BROWSER FOLDERS PANE ?>

				<div class="filebrowser_folders_pane">
			
					<ul class="folder_list">
						<?php 
						$class = '';
						if(!isset($this->params['named']['folder'])) {
							$class .= ' selected';
						}?>
						<li class="<?php echo $class ?>"><?php echo $html->link('[root]','/admin/file_library/file_library_files/index/'.$linkSuffix) ?>
							<ul>

								<?php foreach ($uploadedFileFolders as $folder) : ?>
				
									<?php 
									$class = "";
									if($folder['FileLibraryFolder']['is_smart']) {
										$class .= " smart";
									}
									if(isset($this->params['named']['folder']) && $this->params['named']['folder'] == $folder['FileLibraryFolder']['id']) {
										$class .= " selected";
									}	 
									?>
					
									<li class="<?php echo $class ?>">
									<?php				
									echo $html->link(
									$folder['FileLibraryFolder']['name'],
									'/admin/file_library/file_library_files/index/folder:'.$folder['FileLibraryFolder']['id'].'/'.$linkSuffix); 
									?>
									</li>
								<?php endforeach ?>
							</ul>
						</li>
					</ul>
			
					<?php if(!$inPopup) : ?>
						<p></p>
						<p></p>
						<p><?php echo $html->link('manage folders...','/admin/file_library/file_library_folders/index/'.$linkSuffix)?></p>
					<?php endif ?>
			
				</div>

			<?php ///// END FILE BROWSER FOLDERS PANE ?>
		
			<?php endif ?>
		
			<?php ///// FILE BROWSER FILES PANE ?>
		
			<div class="filebrowser_files_pane">

				<div class="filebrowser_list">

					<?php if ($type == 'file' && $file_type == 'page_link') : ?>

						<?php echo $nestedMenu->generate($files,array(
							'initialDepth' => 999,
							'depthLimit' => 999,
							'model'=>'CorePage',
							'display'=>'title',
							'baseListId'=>'mainmenu',
							'baseListClass'=>'filebrowser_page_list',
							'li_class_prefix'=>'mainmenuop_'));
						 ?>

					<?php else : ?>

						<?php foreach($files as $file) : ?>

							<?php 
							$filename = 'files/'.$file['FileLibraryFile']['id'].'/'.$file['FileLibraryFile']['filename'];
							if($file['FileLibraryFile']['type']=='image') {
								$thumbnail = $html->url('/file_library/file_library_files/thumb/src:').str_replace('/','|',$filename).'/size:s/'; 
							}
							else {
								$thumbnail = $html->url('/file_library/img/document.png'); 
							}
							?>

							<div class="filebrowser_item" id="filebrowser_item_<?php echo $file['FileLibraryFile']['id'] ?>">

								<div class="filebrowser_item_thumb">
									<img src="<?php echo $thumbnail ?>" class="browser_item" />
								</div>

								<div class="filebrowser_item_text">
									<p><strong><?php echo $file['FileLibraryFile']['filename'] ?></strong></p>
								</div>

								<div class="clear"></div>

							</div>

						<?php endforeach ?>

					<?php endif?>

					<div class="clear"></div>

				</div>

			</div>		
		
			<?php ///// END FILE BROWSER FILES PANE ?>
		
			<div class="clear"></div>

		</div>
	
	</div>
	
</div>


<?php 

$base = $html->url('/');

echo $html->scriptBlock(<<<END

	function applyFileLibraryPlugin() {
		$('#filebrowser').filelibrary(
			{
				baseURL:'{$base}',
				ajaxFileInfoURLprefix:'{$base}admin/file_library/file_library_files/info/{$linkSuffix}',
				inPopup:true,
				type:'{$type}',
				fileType:'{$file_type}'
			}
		);
	}

END
);

if($inPopup) {
	
	echo $html->scriptBlock(<<<END
		
		var FileBrowserDialogue = {
				init : function () {
					applyFileLibraryPlugin();
				},
				sendURLBack : function (URL) {
					var win = tinyMCEPopup.getWindowArg("window");

					// insert information now
					win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

					// for image browsers: update image dimensions
					if(tinyMCEPopup.getWindowArg("type") == 'image') {
						if (win.ImageDialog.getImageData) win.ImageDialog.getImageData();
						if (win.ImageDialog.showPreviewImage) win.ImageDialog.showPreviewImage(URL);
					}

					// close popup window
					tinyMCEPopup.close();
				}
			}

			tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);		
	
END
	);

}
else {
	echo $html->scriptBlock('$().ready(applyFileLibraryPlugin);');	
}
	

?>