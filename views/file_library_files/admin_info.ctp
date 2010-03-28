<?php if($inPopup) {
	$linkSuffix = 'context:popup/';
}
else {
	$linkSuffix = '';
}


?>

<?php

	// move these to model functions

	$filename = 'files/'.$file['FileLibraryFile']['id'].'/'.$file['FileLibraryFile']['filename'];
	$abs_filename = WWW_ROOT.$filename;
	$thumbnail = '/file_library/file_library_files/thumb/src:'.str_replace('/','|',$filename).'/size:m/'; 
	$thumbnail_prefix = $html->url('/file_library/file_library_files/thumb/src:').str_replace('/','|',$filename).'/size:';
	
	$this_file = new File($abs_filename);
	
	$dimensions = getimagesize($abs_filename);
	
?>

<h3><?php echo $file['FileLibraryFile']['filename'] ?></h3>
<table>
	<tr><td>filesize:</td><td><?php echo ceil($this_file->size()/1000) ?> KB</td></tr>
	<?php if($dimensions != false) : ?>
		<tr><td>dimensions:</td><td><?php echo $dimensions[0] . ' x ' . $dimensions[1] ?> pixels</td></tr>
	<?php endif ?>
	<!-- <tr><td>created:</td><td><?php echo $time->niceShort( filectime($abs_filename) ) ?></td></tr>
	<tr><td>modified:</td><td><?php echo $time->niceShort( filemtime($abs_filename) ) ?></td></tr> -->
	<tr><td>uploaded:</td><td><?php echo $time->niceShort($file['FileLibraryFile']['created']) ?></td></tr>
	<tr><td>description:</td><td><?php echo $file['FileLibraryFile']['description'] ?></td></tr>
</table>

<p>
<?php echo $html->link('edit file info','/admin/file_library/file_library_files/edit/'.$file['FileLibraryFile']['id'].'/'.$linkSuffix); ?> |
<?php echo $html->link('download','/admin/file_library/file_library_files/download/'.$file['FileLibraryFile']['id'].'/'.$linkSuffix); ?> 
<?php if(!$file['FileLibraryFile']['smart_file']) : ?>
|
<?php echo $html->link('delete','/admin/file_library/file_library_files/delete/'.$file['FileLibraryFile']['id'].'/'.$linkSuffix,array('confirm'=>'Are you sure?')); ?>
<?php endif ?>
</p>


<input type="hidden" id="filebrowser_selected_file_path_prefix" value="<?php echo $thumbnail_prefix ?>" />
<input type="hidden" id="filebrowser_selected_file_path" value="<?php echo $filename ?>" />

<?php if($file['FileLibraryFile']['type']=='image') : ?>
	
	<?php echo $html->image($thumbnail) ?>

<?php endif ?>