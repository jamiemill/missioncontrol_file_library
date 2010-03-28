<?php

/////////////////////////////////////////////////////
// Jamie's image server
//
// v1.3 - 14.7.2008
//
// based on http://sneak.co.nz script
// takes a few parameters and works out which image to find and from where,
// resizes the image if neccessary and stores the output in a cache if this size has never
// been requested before. Otherwise the cached image is output directly.
// $_GET['w'] sets the desired output width, which will be overridden by the limits set below.
//
// I think the cache folder needs to be set to chmod 777
//
// WARNING: this script will not check that the src filepath is not 
//
// TODO:
// if there's an error, perhaps output the error as an image to accomodate for browsers that were
// expecting an image
// add watermarking option
// add optional hotlinking detection
// add option to get unresized image at original dimensions (same as setting v high width)
// accepting any size from $_GET might be a pain as users could potentially request any size and bypass the cache,although the default max width restriction below does set an upper limit
// add option to simply md5 the filepath and arguments and use that as the cache filename

// HISTORY
// 1.1 - 19.3.2008 - gave cache file filenames a limited number of characters from their path, so same-name files from different folders can be differentiated. 
// 1.2 - 19.5.2008 - added check for presence of at least width or height
//		- added scalemodes and renamed crop_anchor to anchor, as it is also used in other scalemodes
//		- modified cache filenames to include scalemode and to always end in .jpg
// 1.2.1 - 19.6.2008 - made bounding box (scaleMode 1) the default
// 1.3 - 14.7.2008 - added allowOverstretch parameter and associated logic to disable overstretching when false
//		- added backgroundColour parameter for when using background fill scale mode



class ImageServer {

	var $src = null; // path to source image	
	var $default_src = null; // falls back to this image if above not found
	var $cache_path = null; // optional, must end in slash
	var $cache_filepath_retain = 40; // max number of characters of the filepath to use in the filename of cached files
	
	var $w = false; // desired width
	var $h = false;// desired height
	var $max_w = 800; // safeguard in case above is being set by query string, overridable by instance
	var $max_h = 800; // safeguard in case above is being set by query string, overridable by instance
	
	
	var $cache_required = true; // allow this script to generate images without a working cache?
	var $sharpen = true; // whether or not to sharpen the image. sometimes resizing it makes it soft.
	var $error; // holds error information.
	
	// anchor point when cropping or bg filling
	var $anchor = false; // C (default) centres the image, TL = top-left, TC = top-centre
	
	// if two dimensions are supplied, scaleMode controls how the image is handled 
	// 0 = zoom and crop to fill, maintains exact requested dimensions by cropping either height or width, unless source is too small and allowOverstretch is false
	// 1 = (default) bounding box, no cropping, maintains original image ratio rather and ensures neither dimension falls outside the imaginary bounding box defined by w and h. width and height will unlikely be exactly what's requested.
	// 2 = background fill mode, no cropping. maintains requested dimensions. image will be stretched to fill either width or height if too small and allowOverstretch is true.
	var $scaleMode = 1; 
	
	var $allowOverstretch = false; // allow thumb to be bigger than source?
	
	var $offset_x = 0; // offset the resized image on its canvas horizontally - useful when image gets cropped - overridden by $anchor
	var $offset_y = 0; // offset the resized image on its canvas vertically - useful when image gets cropped - overridden by $anchor
	
	var $jpgQuality = 100;
	
	var $backgroundColour = array(0xff,0xff,0xff); // array representing RGB colour of background fill (int 0-255 or hex 0x00 - 0xff), for use in "background fill" scaling mode.
	
	
	
	// private vars
	
	var $filename; // basename for cache saving, includes encoded path to ensure cache files don't get mixed up.
	var $desired_width;
	var $desired_height;

	
	function output () {
		
		// constrain submitted width or height to the default maximums
		
		if (!$this->w || $this->w > $this->max_w) {
			$this->desired_width = $this->max_w;
		}
		else {
			$this->desired_width = $this->w;
		}
		
		if (!$this->h || $this->h > $this->max_h) {
			$this->desired_height = $this->max_h;
		}
		else {
			$this->desired_height = $this->h;
		}
		

	
		
		// check file exists, if not try default alternative image
		if(!file_exists($this->src)) {
			if(file_exists($this->default_src)) {
				$this->src = $this->default_src;
			}
			else {
				$this->error = "No image or default image found.";
				return false;
			}
		}
		// filename for cache
		//$this->filename = basename($this->src);
		$this->filename = substr(preg_replace('/[^a-zA-Z0-9\.\_]/','-',$this->src),-$this->cache_filepath_retain);

		// read the source dimensions

		if(!$size = @getimagesize($this->src)) {
			$this->error = "File not readable.";
			return false;
		}
		
		$src_width = $size[0];
		$src_height = $size[1];
		$src_aspect = $src_width / $src_height;
		
		if(!$this->w && !$this->h) {
			$this->error = "Must specify either a height or width.";
			return false;
		}
		

		
		
		// if we were only given a single desired dimension. 
		// can't remember why but this mode seems to bock overstretching the image to a larger thumb
		
		if(!$this->w || !$this->h) {

			// find the dimensional ratios of desired comapared to source
			$x_ratio = $this->desired_width / $src_width;
			$y_ratio = $this->desired_height / $src_height;

			// if image already meets criteria, load current values in
			// if not, use ratios to load new size info
		
			// if both dimensions are smaller than the desired size, use the source dimensions - no scaling up will occur
			if (($src_width <= $this->desired_width) && ($src_height <= $this->desired_height) ) { 
				$tn_width = $src_width;
				$tn_height = $src_height;
			}
			// if we scale based on width, will the resultant height fit in the desired height?
			else if (($x_ratio * $src_height) < $this->desired_height) { 
				$tn_height = ceil($x_ratio * $src_height);
				$tn_width = $this->desired_width;
			}
			// it didn't, so scale based on height instead
			else {										
				$tn_width = ceil($y_ratio * $src_width);
				$tn_height = $this->desired_height;
			}
		
			$canvas_width = $tn_width;
			$canvas_height = $tn_height;
		}
		
		// if we were supplied with two dimensions and we are zoom-cropping to fill
		
		elseif ($this->w && $this->h && $this->scaleMode == 0) {
			
			// correct the dimensions if the image is actually smaller than the desired thumbnail.
			// results in the canvas being smaller than requested if image is too small
			if($this->allowOverstretch == false) {
				if($this->desired_width > $src_width) {
					$this->desired_width = $src_width;
				}
				if($this->desired_height > $src_height) {
					$this->desired_height = $src_height;
				}
			}
			
			$canvas_width = $this->desired_width;
			$canvas_height = $this->desired_height;
			
			// find the dimensional ratios of desired comapared to source
			$x_ratio = $this->desired_width / $src_width;
			$y_ratio = $this->desired_height / $src_height;
			
			// if the image is too tall to fit the thumbnail's proportions
			if ($x_ratio > $y_ratio) {
				$tn_width = $this->desired_width;
				$tn_height = round($this->desired_width / $src_aspect);
			}
			// if the image is too wide to fit the thumbnail's proportions
			else {
				$tn_height = $this->desired_height;
				$tn_width = round($this->desired_height * $src_aspect);
			}			
		}
		
		// bounding-box mode, thumb has same proportions as source
		
		elseif($this->w && $this->h && $this->scaleMode==1) {
			
			// correct the dimensions if the image is actually smaller than the desired thumbnail.
			// results in the canvas being smaller than requested if image is too small,
			// which is probably OK as bounding box mode usually has one dimension smaller than desired.
			if($this->allowOverstretch == false) {
				if($this->desired_width > $src_width) {
					$this->desired_width = $src_width;
				}
				if($this->desired_height > $src_height) {
					$this->desired_height = $src_height;
				}
			}

			// find the dimensional ratios of desired compared to source
			$x_ratio = $this->desired_width / $src_width;
			$y_ratio = $this->desired_height / $src_height;
			
			// if the image is too tall to fit the thumbnail's proportions
			if ($x_ratio > $y_ratio) {
				$tn_height = $this->desired_height;
				$tn_width = round($this->desired_height * $src_aspect);

			}
			// if the image is too wide to fit the thumbnail's proportions
			else {
				$tn_width = $this->desired_width;
				$tn_height = round($this->desired_width / $src_aspect);
			}
			$canvas_width = $tn_width;
			$canvas_height = $tn_height;
			
		}
		
		// background fill mode to respect requested dimensions without cropping source.
		
		elseif($this->w && $this->h && $this->scaleMode==2) {
			
			$canvas_width = $this->desired_width;
			$canvas_height = $this->desired_height;
			
			// correct the dimensions if the image is actually smaller than the desired thumbnail.
			// this calculation occurs after the canvas size is set (unlike the above modes) so that teh canvas size always stays untouched
			
			if($this->allowOverstretch == false) {
				if($this->desired_width > $src_width) {
					$this->desired_width = $src_width;
				}
				if($this->desired_height > $src_height) {
					$this->desired_height = $src_height;
				}
			}
			
			// find the dimensional ratios of desired compared to source
			$x_ratio = $this->desired_width / $src_width;
			$y_ratio = $this->desired_height / $src_height;
			
			// if the image is too tall to fit the thumbnail's proportions
			if ($x_ratio > $y_ratio) {
				$tn_height = $this->desired_height;
				$tn_width = round($this->desired_height * $src_aspect);
			}
			// if the image is too wide to fit the thumbnail's proportions
			else {
				$tn_width = $this->desired_width;
				$tn_height = round($this->desired_width / $src_aspect);
			}
			
		}
		else {
			$this->error = "Scalemode not recognised";
			return false;
		}
		
		
		// recenter if necessary
		
		if($this->w && $this->h && $this->anchor != false && 
			($this->scaleMode == 0 || $this->scaleMode == 2)) {
			switch ($this->anchor) {
					
				case 'TL' :
					$this->offset_x = 0;
					$this->offset_y = 0;
				case 'TC' :
					$this->offset_x = - round(($tn_width - $canvas_width) / 2);
					$this->offset_y = 0;
					break;
				case 'C' :
				default :
					$this->offset_x = - round(($tn_width - $canvas_width) / 2);
					$this->offset_y = - round(($tn_height - $canvas_height) / 2);
					break;
				
			}
		}
		
		
		
		
		
		$cached_image_path = $this->cache_path.$this->w.'x'.$this->h.'-'.$this->anchor.'-'.$this->scaleMode.'--'.$this->filename.'.jpg'; // filename for cache
		
		// if cache is enabled and there's already an image
		if(!empty($this->cache_path) && file_exists($cached_image_path)) {
			
			$this->srcModified = @filemtime($this->src);
			$thumbModified = @filemtime($cached_image_path);

			// if thumbnail is newer than image then output cached thumbnail and exit
			if($this->srcModified<$thumbModified) {
				header("Content-Type: image/jpeg"); // add other file types?
				header("Content-Length: ".filesize($cached_image_path)); // add other file types?
				header("Last-Modified: ".gmdate("D, d M Y H:i:s",$thumbModified)." GMT");
				readfile($cached_image_path);
				return true;
			}
		}




		// if cache file did not exist or was out of date, create a new one
		
		$ext = substr(strrchr($this->src, '.'), 1); // get the file extension
		switch ($ext) { 
			case 'jpg':     // jpg
				$src = imagecreatefromjpeg($this->src) or notfound();
				break;
			case 'png':     // png
				$src = imagecreatefrompng($this->src) or notfound();
				break;
			case 'gif':     // gif
				$src = imagecreatefromgif($this->src) or notfound();
				break;
			default:
				$this->error = 'unrecognised file extension';
				return false;
		}

		// set up canvas
		$dst = imagecreatetruecolor($canvas_width,$canvas_height);
		
		if($this->scaleMode == 2) {
			$bgCol = imagecolorallocate($dst, $this->backgroundColour[0],$this->backgroundColour[1],$this->backgroundColour[2]);
			imagefill($dst, 0, 0, $bgCol);
		}

		imageantialias ($dst, true);

		// copy resized image to new canvas
		imagecopyresampled ($dst, $src, $this->offset_x, $this->offset_y, 0, 0, $tn_width, $tn_height, $src_width, $src_height);
		
		if($this->sharpen) {
			/* Sharpening adddition by Mike Harding */
			// sharpen the image (only available in PHP5.1)
			if (function_exists("imageconvolution")) {
				$matrix = array(    array( -1, -1, -1 ),
			                    array( -1, 32, -1 ),
			                    array( -1, -1, -1 ) );
				$divisor = 24;
				$offset = 0;

				imageconvolution($dst, $matrix, $divisor, $offset);
			}
		}
		
		// attempt to save it to cache
		
		if(!empty($this->cache_path) || $this->cache_required) {
			if(is_writable($this->cache_path)) {
				imagejpeg($dst, $cached_image_path, $this->jpgQuality); // write the thumbnail to cache as well...
			}
			else {
				$this->error = 'Cache path set or required but not writable.';
				return false;
			}
			
			
		}
		
		// send the header and new image
		header("Content-type: image/jpeg");
		imagejpeg($dst, null, $this->jpgQuality);
		
		// clear out the resources
		imagedestroy($src);
		imagedestroy($dst);
	}
	
}

?>