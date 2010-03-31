<?php

/*

TODO: can we add a relationship at runtime? we will need the model to "belongTo" FileLibraryFile so that we can 
TODO: update this to handle when UPDATING , not just CREATING


Usage:

the model this is applied to should have an file_id or simlar field to store a link to the resulting uploaded file record


var $actsAs = array(
	'FileLibraryFile'=>array(
		'form_field_id' => 'database_field_for_resulting_id'
		'image_upload'=>'image_file_id', // example
		'CV_upload'=>'cv_file_id',	   // example
		)
);

*/


class FileAttachedBehavior extends ModelBehavior {
	var $settings = array();
	
	var $_defaults = array(
	);
	
	var $FileLibraryFile = null;
	

	function setup(&$model, $settings = array()) {
		$settings = (array)$settings;
		//$settings = array_merge($this->_defaults, $settings);	
		$this->settings[$model->name] = $settings;
		
		App::import('model','FileLibrary.FileLibraryFile');
		$this->FileLibraryFile = new FileLibraryFile;
	}

	function afterSave(&$model,$created) {
		
		if(empty($this->settings[$model->name])) {
			return true;
		}
		
		$folderName = Inflector::humanize(Inflector::underscore($model->name)).' Attachments';
		
		$folder = $this->FileLibraryFile->FileLibraryFolder->find('first',array('conditions'=>array('name'=>$folderName,'is_smart'=>true)));
		if(empty($folder)) {
			$newFolder = array('FileLibraryFolder'=>array('name'=>$folderName,'is_smart'=>true));
			$this->FileLibraryFile->FileLibraryFolder->create();
			$this->FileLibraryFile->FileLibraryFolder->save($newFolder);
			$folderId = $this->FileLibraryFile->FileLibraryFolder->id;
		}
		else {
			$folderId = $folder['FileLibraryFolder']['id'];
		}
		
		$idsToSave = array();
		
		foreach($this->settings[$model->name] as $formField => $dbField) {
			if(!empty($model->data[$model->name][$formField]['name'])) {
				$data = array('FileLibraryFile'=>array());

				$data['FileLibraryFile']['file_upload'] = $model->data[$model->name][$formField];

				$data['FileLibraryFile']['file_library_folder_id'] = $folderId;
				$data['FileLibraryFile']['smart_file'] = true;

				$this->FileLibraryFile->create();
				$this->FileLibraryFile->save($data);
				
				$idsToSave[$dbField] = $this->FileLibraryFile->id;
			}
		}

		$model->save($idsToSave,array('callbacks'=>false));	
		return true;
	}

	function beforeDelete(&$model){	
		foreach($this->settings[$model->name] as $formField => $dbField) {
			$id = $model->field($dbField);
			$this->FileLibraryFile->delete($id);
		}
	}
	

	
}
?>
