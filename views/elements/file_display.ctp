<?php

App::import('model','FileLibrary.FileLibraryFile');
$FileLibraryFile = new FileLibraryFile;

$FileLibraryFile->id = $id;
$filename = $FileLibraryFile->field('filename');

$options = array();
if(!empty($alt)) {
	$options['alt'] = $alt;
}
if(!empty($url)) {
	$options['alt'] = $url;
}

if(!isset($size)) {
	$size = 'm';
}
if(isset($original)) {
	$path = '/files/'.$id.'/'.$filename;
} else {
	$path = '/file_library/file_library_files/thumb/src:files|'.$id.'|'.$filename.'/size:'.$size;
}

if(isset($pathOnly)) {
	echo $path;
}
else {
	echo $html->image($path, $options);
}



?>
