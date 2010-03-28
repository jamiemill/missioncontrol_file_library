<?php
class FileLibraryFile extends FileLibraryAppModel {
	
	var $name = 'FileLibraryFile';
	

	var $validate = array(
		'file_upload' => array(
            'rule' => array('checkForFile','file_upload'),
            'message' => 'ERROR: No file selected.',
			'on'=>'create'
     	)
	);
			
	var $belongsTo = array('FileLibrary.FileLibraryFolder');
	
	// which extensions to treat as images. files with these extensions will be made available in the image picker
	
	var $imgExts = array('jpg','jpeg','png','gif');

	function checkExtIfUploaded($field_data,$field_name = 'file_upload',$exts = array('jpg','jpeg')) {
        $valid = false;

		if(!$field_data[$field_name]['size']) {
			// no file was uploaded
			$valid = true;
		}
		else {
			$ext = strtolower(array_pop(explode('.',$field_data[$field_name]['name'])));
			if(in_array($ext,$exts)) {
				$valid = true;
			}
		}
        return $valid;
   }

	function checkForFile($field_data,$field_name = 'file_upload') {
      $valid = false;

		if(isset($field_data[$field_name]['size']) && $field_data[$field_name]['size']) {
			// file was uploaded
			$valid = true;
		}
      return $valid;
	}


	function findImages($type,$options=null) {
		$results_temp = $this->find($type,$options);
		$results = array();
		foreach($results_temp as $res) {
			
			$ext = strtolower(array_pop(explode('.',$res['FileLibraryFile']['filename'])));
			if(in_array($ext,$this->imgExts)) {
				$results[] = $res;
			}
		}
		return $results;
	}

	function beforeSave() {
		// sanitize the filename before saving to database so we don't have problems with filenames in the URL later
		// copy it to the correct database field
		// TODO: maybe dont need to store the "type", better to check at runtime against an up-to-date list of file types?
		
		if(!empty($this->data['FileLibraryFile']['file_upload']['name'])) {
			$this->data['FileLibraryFile']['file_upload']['name'] = $this->dullText($this->data['FileLibraryFile']['file_upload']['name']);
			$this->data['FileLibraryFile']['filename'] = $this->data['FileLibraryFile']['file_upload']['name'];
			
			// also set the file type: image or document
			$ext = strtolower(array_pop(explode('.',$this->data['FileLibraryFile']['file_upload']['name'])));
			if(in_array($ext,$this->imgExts)) {
				$this->data['FileLibraryFile']['type'] = 'image';
			}
			else{
				$this->data['FileLibraryFile']['type'] = 'document';
			}
		}
		
		return true;
	}

	// perform upload after save so that we know the inserted ID

	function afterSave() {
		if(!empty($this->data['FileLibraryFile']['file_upload']['name'])) {
			
			$file_array = $this->data['FileLibraryFile']['file_upload'];
			
			$this->log('Found an uploaded file. '.$file_array['name'],LOG_DEBUG);
			
			$new_folder = WWW_ROOT.'files'.DS.$this->id;
			
			if(file_exists($new_folder)) {
				$this->rmdirRecursive($new_folder);
			}
			mkdir($new_folder);
			
			$new_filename = $new_folder.DS.basename($file_array['name']);
			
			if(move_uploaded_file($file_array['tmp_name'],$new_filename)) {
				chmod($new_filename, 0644);
				// successful upload complete
				$this->log('Moved file to '.$new_filename,LOG_DEBUG);
			}
			else {
				$this->log('Could not move file to '.$new_filename,LOG_DEBUG);
			}
		}
		return true; // return true as this upload is non-essential and performed after save
	}
	
	// function for stripping unusual characters from filenames, a bit brutal but works well.
	function dullText($string,$replacement = '') {
		$string = preg_replace('/[^a-z0-9_ \.\-]/i', $replacement, $string);
		return $string;
	}

	function afterDelete(){
		$path = WWW_ROOT.'files'.DS.$this->id;
		if(file_exists($path)) {
			$this->rmdirRecursive($path);
		}
	}
	
	function rmdirRecursive($path,$followLinks=false) {

	    $dir = opendir($path) ;
	    while ( $entry = readdir($dir) ) {

	        if ( is_file( "$path/$entry" ) || ((!$followLinks) && is_link("$path/$entry")) ) {
	            //echo ( "unlink $path/$entry;\n" );
	            // Uncomment when happy!
	            @unlink( "$path/$entry" );
	        } elseif ( is_dir( "$path/$entry" ) && $entry!='.' && $entry!='..' ) {
	            $this->rmdirRecursive( "$path/$entry" ) ;
	        }
	    }
	    closedir($dir) ;
	    //echo "rmdir $path;\n";
	    // Uncomment when happy!
	    return @rmdir($path);
	}

}
?>
