<?php
class FileLibraryFolder extends FileLibraryAppModel {

	var $name = 'FileLibraryFolder';
	var $validate = array(
		'name' => array('notempty')
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $hasMany = array('FileLibraryFile'=>array('className'=>'FileLibrary.FileLibraryFile','dependent'=>true));

}
?>
