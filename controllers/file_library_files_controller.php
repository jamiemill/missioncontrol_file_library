<?php

class FileLibraryFilesController extends FileLibraryAppController {

	var $layout = 'admin';
	
	var $components = array('RequestHandler');
	
	function beforeFilter() {
		parent::beforefilter();
		$this->Auth->allow('download','thumb');
	}
	

	
	// for the file browser
	// namedparam:context = popup | undefined -> $inPopup
	// namedparam:type = file | image (default = file)
	// namedparam:file_type = page_link | file_download
	
	function admin_index() {
		
		if(isset($this->params['named']['context']) && $this->params['named']['context'] == 'popup') {
			$this->layout = 'admin_popup';
			$inPopup = true;
		}
		else {
			$inPopup = false;
		}
		if(!isset($this->params['named']['type'])) {
			$this->params['named']['type'] = 'file';
		}
		
		$folder = null;
		if(isset($this->params['named']['folder'])) {
			$folder = $this->params['named']['folder'];
			if(!isset($this->params['named']['file_type'])) {
				$this->params['named']['file_type'] = 'file_download';
			}
		}
		

		
		if(!isset($this->params['named']['file_type'])) {
			if($inPopup) {
				$this->params['named']['file_type'] = 'page_link';
			}
			else {
				$this->params['named']['file_type'] = 'file_download';
			}
		}

		
		// now get the data //////////

		if($this->params['named']['type'] == 'file' && $this->params['named']['file_type'] == 'page_link') {
			$result = $this->requestAction('/admin/core/core_pages/sitemap');
		}
		elseif(($this->params['named']['type'] == 'file' && $this->params['named']['file_type'] == 'file_download' )
			|| $this->params['named']['type'] == 'image') {
				
			$result = $this->FileLibraryFile->find('all',array('order'=>'FileLibraryFile.filename ASC','conditions'=>array('file_library_folder_id'=>$folder)));
		}
		elseif($this->params['named']['type'] == 'media') {

		}
		else {
			$result = $this->FileLibraryFile->find('all',array('order'=>'FileLibraryFile.title ASC'));
		}
		
		////////////////////////

		$this->set('thumbSizes',$this->sizes);
		
		$this->set('files',$result);
		$this->set('type',isset($this->params['named']['type']) ? $this->params['named']['type'] : '');
		$this->set('file_type',$this->params['named']['file_type']);
		$this->set('inPopup',$inPopup);
		$this->set('uploadedFileFolders',$this->FileLibraryFile->FileLibraryFolder->find('all',array('order'=>'name ASC')));
		$this->set('type',$this->params['named']['type']);
	}
	
	
	
	function admin_info($id) {
		
		if(isset($this->params['named']['context']) && $this->params['named']['context'] == 'popup') {
			$this->set('inPopup',true);
		}
		else {
			$this->set('inPopup',false);
		}
		
		if ($this->RequestHandler->isAjax()) {
				Configure::write('debug', 0);
				header('Pragma: no-cache');
				header('Cache-control: no-cache');
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		}
			
		$result = $this->FileLibraryFile->find(array('FileLibraryFile.id'=>$id));
		
		$this->set('file',$result);
		
		$this->set('filetype',isset($this->params['named']['type']) ? $this->params['named']['type'] : '');
	}
	
	
	function admin_add($user_id = '') {
		if(!isset($this->params['named']['type'])) {
			$this->params['named']['type'] = 'all';
		}
		$this->set('type',$this->params['named']['type']);
		
		if(!isset($this->params['named']['file_type'])) {
			$this->params['named']['file_type'] = 'file_download';
		}
		$this->set('file_type',$this->params['named']['file_type']);
		
		
		if(isset($this->params['named']['context']) && $this->params['named']['context'] == 'popup') {
			$this->layout = 'admin_popup';
			$this->set('inPopup',true);
		}
		else {
			$this->set('inPopup',false);
		}
		
		if(!empty($this->data)) {
			if( $this->FileLibraryFile->save($this->data) ) {
				if(isset($this->data['FileLibraryFile']['from_popup'])) {
					$this->_smartFlash(true, array('action' => 'index', 'context' => 'popup'));
				}
				else {
					$this->_smartFlash(true, array('action' => 'index'));
				}
			}
		}	
		$this->set('fileLibraryFolders',$this->FileLibraryFile->FileLibraryFolder->find('list',array('conditions'=>array('is_smart'=>false),'order'=>'name ASC')));
	}
	
	function admin_edit($id) {
		
		if(!isset($this->params['named']['type'])) {
			$this->params['named']['type'] = 'all';
		}
		$this->set('type',$this->params['named']['type']);
		
		if(!isset($this->params['named']['file_type'])) {
			$this->params['named']['file_type'] = 'file_download';
		}
		$this->set('file_type',$this->params['named']['file_type']);
		
		
		if(isset($this->params['named']['context']) && $this->params['named']['context'] == 'popup') {
			$this->layout = 'admin_popup';
			$this->set('inPopup',true);
		}
		else {
			$this->set('inPopup',false);
		}
		
		if(!empty($this->data)) {
			if( $this->FileLibraryFile->save($this->data) ) {
				if(isset($this->data['FileLibraryFile']['from_popup'])) {
					$this->_smartFlash('File updated.','/admin/file_library/file_library_files/index/context:popup');
				}
				else {
					$this->_smartFlash('File updated.','/admin/file_library/file_library_files/index/');
				}
			}
		}
		
		if(empty($this->data)) {
			$this->data = $this->FileLibraryFile->read(null, $id);
		}
		$this->set('fileLibraryFolders',$this->FileLibraryFile->FileLibraryFolder->find('list',array('conditions'=>array('is_smart'=>false),'order'=>'name ASC')));
	}
	
	function admin_delete($id) {
		
		if( $this->FileLibraryFile->delete($id) ) {
			
			if(isset($this->params['named']['context']) && $this->params['named']['context'] == 'popup') {
				$this->_smartFlash('File deleted.','/admin/file_library/file_library_files/index/context:popup');
			}
			else {
				$this->_smartFlash('File deleted.','/admin/file_library/file_library_files/index');
			}
		}
	}
	

	
	// new download function using mediaview to help with mimetypes, IE compatibility, buffering etc
	
	function download($id = null) {
		//Configure::write('debug', 0);
		$this->FileLibraryFile->id = $id;
		$download = true;

		$this->view = 'Media';
		$this->autoLayout = false;
		$this->FileLibraryFile->recursive = -1;
		$media = $this->FileLibraryFile->read();

		if (empty($media)) {
			header('Content-Type: text/html');
			$this->cakeError('error404');
			return false;
		} 
		// no need for permissions in this application
		// consider re-enabling and adding a check for a "public" boolean option to the uploaded_files model
		// elseif($media['FileLibraryFile']['user_id'] != $this->Auth->user('id') && $this->Auth->user('administrator') != true) {
		// 	$this->_smartFlash('Sorry, you don\'t have permission to download that file.','/');
		// }
		else {
			$extension = strtolower(substr(strrchr($media['FileLibraryFile']['filename'], '.'), 1));

			$ext = strrchr($media['FileLibraryFile']['filename'], '.');
			if($ext !== false) {
				$name = substr($media['FileLibraryFile']['filename'], 0, -strlen($ext));
			}

			$path = WWW_ROOT.'files'.DS.$media['FileLibraryFile']['id'].DS;
			$filename = $media['FileLibraryFile']['filename'];

			$this->set('name', $name);
			$this->set('id', $filename); // this gets concatenated on end of path so treat it like a filename
			$this->set('download', $download);
			$this->set('extension', $extension);
			$this->set('modified', $media['FileLibraryFile']['modified']);
			$this->set('path', $path);
			$this->set('mimeType', array('bmp'=>'image/bmp')); // add any missing mime types here
		}

	}
	
	function admin_download($id = null) {
		Configure::write('debug', 0);
		$this->FileLibraryFile->id = $id;
		$download = true;

		$this->view = 'NewMedia'; // use jamie's modified MediaView
		$this->autoLayout = false;
		$this->FileLibraryFile->recursive = -1;
		$media = $this->FileLibraryFile->read();

		if (empty($media)) {
			$this->redirect('/', '404', true);
		} 
		// no need for permissions in this application
		// consider re-enabling and adding a check for a "public" boolean option to the uploaded_files model
		// elseif($media['FileLibraryFile']['user_id'] != $this->Auth->user('id') && $this->Auth->user('administrator') != true) {
		// 	$this->_smartFlash('Sorry, you don\'t have permission to download that file.','/');
		// }
		else {
			$extension = strtolower(substr(strrchr($media['FileLibraryFile']['filename'], '.'), 1));

			$ext = strrchr($media['FileLibraryFile']['filename'], '.');
			if($ext !== false) {
				$name = substr($media['FileLibraryFile']['filename'], 0, -strlen($ext));
			}
			// for mediaView, path must be relative to APP
			$path = 'webroot'.DS.'files'.DS.$media['FileLibraryFile']['id'].DS.$media['FileLibraryFile']['filename'];

			$this->set('name', $name);
			$this->set('download', $download);
			$this->set('extension', $extension);
			$this->set('modified', $media['FileLibraryFile']['modified']);
			$this->set('path', $path);
			
			// these two lines are used for some file-naming convention we're not interested in at the moment
			//$this->set('id', $media['FileLibraryFile']['id']); 
			//$this->set('size', filesize($path));
			
		}

	}
	
	function download_old($id) {
		
		$result = $this->FileLibraryFile->findById($id);
		
		if(empty($result)) {
			$this->_smartFlash('Sorry, that file was not found.','/');
		}
		elseif($result['FileLibraryFile']['user_id'] != $this->Auth->user('id') && $this->Auth->user('administrator') != true) {
			
			$this->_smartFlash('Sorry, you don\'t have permission to download that file.','/');
		}
		else {
			
			$filepath = WWW_ROOT.'files'.DS.$result['FileLibraryFile']['id'].DS.$result['FileLibraryFile']['filename'];
			if (file_exists($filepath)) {	
				
				// MimeType class distributed with Attachments Behaviour
				App::import('Vendor','Mimetype');
					
				header('Content-type: '.Mimetype::detectFast($filepath));		
				header('Content-Disposition: attachment; filename="'.$result['FileLibraryFile']['filename'].'"');
				readfile($filepath);
				exit;
			}
			else {
				$this->_smartFlash('Sorry, that file was not found.','/');
			}
		}
	}
	
	function thumb() {
		
		Configure::write('Security.level', 'medium');
		Configure::write('debug', 0);
		
		$this->layout = null; 
	    $this->autoRender = false;

        if(empty($this->params['named']['src'])){ 
            die("No source image"); 
        } 

		$this->params['named']['src'] = rawurldecode($this->params['named']['src']); // internet explorer was submitting the "|" characters encoded. note that the "|" is intact with other browsers / email clients
          
		$src = str_replace('|',DS,$this->params['named']['src']); 
	
		if($this->params['named']['size'] == 'original') {
			$this->redirect('/'.$src);
			return;
		}

		// sanitize the src name a little
		$src = str_replace('..','',$src); // remove any double dots, 
		// this should probably do enough seeing as we're tagging it onto the end of the WWW_ROOT constant
		// but just in case also filter out other mischievous characters...
		$src = preg_replace('/(^\/)|(^\.\/)|(~)/','',$src); // remove any starting / or ./
	
	
		// width and height disabled in favour of a more secure size matrix
	          // width 
	          // $width = (!isset($this->params['named']['w'])) ? null : $this->params['named']['w']; 
	          // height 
	          // $height = (!isset($this->params['named']['h'])) ? null : $this->params['named']['h']; 		

		$sizecode = isset($this->params['named']['size']) ? $this->params['named']['size'] : null;
	
		// width 
	          $width = (array_key_exists($sizecode,$this->sizes)) ? $this->sizes[$sizecode][0] : 100;
	          // height 
	          $height = (array_key_exists($sizecode,$this->sizes)) ? $this->sizes[$sizecode][1] : 100;

		$scaleMode = (array_key_exists($sizecode,$this->sizes)) ? $this->sizes[$sizecode][2] : 1;

		$anchor = (array_key_exists($sizecode,$this->sizes)) ? $this->sizes[$sizecode][3] : 'C';

           
	          $sourceFilename = WWW_ROOT.$src;
		$maxSrcPixels = 10000000; // images over around 3megapixels seem to exhaust a memory limit of ??MB
	
		if(!file_exists($sourceFilename) || !is_file($sourceFilename)) {
			$sourceFilename = APP.'plugins'.DS.'file_library'.DS.'webroot'.DS.'img'.DS.'admin'.DS.'no-image.png';
		}
	
		$ext = strtolower(substr(strrchr($sourceFilename, '.'), 1)); // get the file extension
		if(!in_array($ext,array('jpg','jpeg','png','gif'))) {
			$sourceFilename = APP.'plugins'.DS.'file_library'.DS.'webroot'.DS.'img'.DS.'admin'.DS.'image-unknown-format.png';
		}
	
		// this image size check is probably slowing the script down. better to check for ready-made thumbnail first
		$imgsize = getimagesize($sourceFilename);
		if(empty($imgsize)) {
			die("Could not check size of source image with getimagesize()");
		}
		if($imgsize[0] * $imgsize[1] > $maxSrcPixels) {
			$sourceFilename = APP.'plugins'.DS.'file_library'.DS.'webroot'.DS.'img'.DS.'admin'.DS.'image-too-large.png';
		}
	
	
	          if(is_readable($sourceFilename)){ 
	          	//vendor("imageserver/imageserver.class");
			$result = App::import('Vendor', 'FileLibrary.ImageServer', array('file' => 'imageserver'.DS.'imageserver.1.3.php'));

	              $i = new ImageServer;
			$i->src = $sourceFilename;
			$i->cache_path = CACHE.'thumbs'.DS; 
			//$i->cache_required = false;
			$i->h = $height;
			$i->w = $width;
			$i->anchor = $anchor;
			$i->cache_required = true;
			$i->max_source_pixelcount = $maxSrcPixels; 
			$i->scaleMode = $scaleMode;
			$i->attempt_memory_increase = 50000000; // false or integer in bytes
			$i->backgroundColour = array(0xFF,0xFF,0xFF); //FFFFFF
		
			// only uncomment if debugging
			/*
			if (function_exists('memory_get_peak_usage')) {
				$mem_peak = memory_get_peak_usage();
				$this->log('Displayed (and maybe created) a thumbnail, memory use was '.$mem_peak.' bytes',LOG_DEBUG);
			}
			else {
				$this->log('Displayed a thumbnail but unable to log max memory use. Current is '.memory_get_usage().' bytes',LOG_DEBUG);
			}*/
		
		
		
		
			if(!$i->output()) {
				echo $i->error;
			}
           
           
	          } else { // Can't read source 
	              die("Couldn't read source image ".$sourceFilename); 
	          } 
	      }
	
}

?>
