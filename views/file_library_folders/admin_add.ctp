<?php

$linkSuffix = '';

if($inPopup) {
	$linkSuffix .= 'context:popup/';
}

?>

<div class="main">
	<div class="box">
		<div class="box_head">
			<h2>Add folder</h2>
		</div>
		<div class="box_content">

			<?php echo $form->create('FileLibraryFolder');?>


			<?php echo $form->input('name'); ?>

			<?php echo $form->submit('Save');?>

			<?php echo $form->end();?>
	
		</div>
	</div>
</div>