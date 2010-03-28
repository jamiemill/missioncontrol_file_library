<?php 

// type can be image, file or file_download
// file is for internal page links

$linkSuffix = '';
if($inPopup) {
	$linkSuffix .= 'context:popup/';
}
$linkSuffix .= 'type:'.$type.'/';

?>

<?php 
$html->addCrumb('File Library',array('controller'=>'file_library_files','action'=>'index'));
$html->addCrumb('add');
?>
<?php echo $this->element('crumb_heading', array('plugin'=>'core'))?>

<div class="main">
	<div class="box">
		<div class="box_head">
			<h2><?php __('Add File') ?></h2>
		</div>
		<div class="box_content">

			<?php echo $form->create('FileLibraryFile',array('type'=>'file')) ?>

				<?php echo $form->input('file_upload',array('type'=>'file','id'=>'uploadFileField')) ?>
		
				<?php echo $form->input('description') ?>
		
				<?php echo $form->input('file_library_folder_id',array('empty'=>'[none]','label'=>'Folder')) ?>

				<?php if($inPopup) : ?>
					<?php echo $form->input('from_popup',array('type'=>'hidden','value'=>true))?>
				<?php endif ?>
		
				<?php echo $form->submit('save')?>
	
			<?php echo $form->end()?>

		</div>
	</div>
</div>