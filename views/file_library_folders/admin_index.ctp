<?php

$linkSuffix = '';
if($inPopup) {
	$linkSuffix .= 'context:popup/';
}

?>

<?php echo $this->element('record_navigation', array('plugin'=>'core'))?>
<?php 
$html->addCrumb('File Library');
?>
<?php echo $this->element('crumb_heading',array('plugin'=>'core'))?>

<div class="main">
	<div class="box">
		<div class="box_head">
			<h2><?php __('All folders') ?></h2>
		</div>
		<div class="box_content">

			<p><?php echo $html->link('< back to file library',array('controller'=>'file_library_files','action'=>'index'))?></p>
			
			<?php echo $this->element('paging_info',array('plugin'=>'core')) ?>
			<?php echo $this->element('paging',array('plugin'=>'core')) ?>
			
			<table class="admin_listing">
				<tr>
					<th><?php echo $paginator->sort('name');?></th>
					<th class="actions"><?php __('Actions');?></th>
				</tr>
				<?php foreach ($uploadedFileFolders as $uploadedFileFolder) : ?>
					<tr>
						<td>
							<?php echo $uploadedFileFolder['FileLibraryFolder']['name'] ?>
						</td>
						<td class="actions">
							<?php echo $html->link(__('Edit', true), array('action'=>'edit', $uploadedFileFolder['FileLibraryFolder']['id'])); ?>
							<?php echo $html->link(__('Delete', true), array('action'=>'delete', $uploadedFileFolder['FileLibraryFolder']['id']), null, sprintf(__('Are you sure you want to delete the folder and ALL its contents?', true), $uploadedFileFolder['FileLibraryFolder']['id'])); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>

