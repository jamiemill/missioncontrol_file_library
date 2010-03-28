<?php

App::import('controller','Core.CoreAppController');

class FileLibraryAppController extends CoreAppController {
	
	// array of size presets, [width,height,scaleMode,anchor] for each
	var $defaultSizes = array(
		'original'=>array(null,null,null,null),
		'xs'=>array(50,50,1,'C'),
		's'=>array(100,100,1,'C'),
		'm'=>array(250,250,1,'C'),
		'l'=>array(400,400,1,'C'),
		'xl'=>array(600,600,1,'C'),
		'gallery_thumb'=>array(98,53,0,'C'),
		'gallery'=>array(530,390,1,'C'),
		);
		
	var $sizes = array();
	
	function __construct() {
		parent::__construct();
		$siteSizes = Configure::read('Site.extraThumbnailSizes');
		if(!empty($siteSizes)) {
			$this->sizes = array_merge($this->defaultSizes,$siteSizes);
		}
		else {
			$this->sizes = $this->defaultSizes;
		}
	}

}
?>
