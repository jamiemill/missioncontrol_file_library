<?php

/**
 * Please note this layout is duplicated inside each plugin, as it doesn't seem possible for plugins to share layouts.
 * $this->layout doesn't support Plugin.file syntax, and $this->layoutPath is relative to the app's layout directory
 * - could possibly use layoutPaths in bootstrap to point at a shared directory?
 */


?>

<?php echo $html->docType(); ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php echo $html->charset(); ?> 
		<?php echo $html->css('/core/css/reset'); ?> 
		<?php echo $html->css('/core/css/admin'); ?> 
		<?php echo $html->css('/file_library/css/admin.css?v=2'); ?> 
		<?php echo $html->script('/core/js/jquery-1.3.2.min.js'); ?> 
		<?php echo $html->script('/core/js/jquery-ui-1.7.2.min'); ?>
		<?php echo $html->script('/core/js/jquery.expandibox.js'); ?>
		<?php echo $html->script('/file_library/js/jquery.filelibrary.js?v=2'); ?>
		<?php echo $html->script('tiny_mce/jquery.tinymce'); ?> 
		<?php echo $html->script('tiny_mce/tiny_mce_popup'); ?>
		<?php echo $html->script('/core/js/global.js?v=2'); ?>
		<?php 
		$base = $html->url('/');
		echo $html->scriptBlock(<<<END
			baseURL = '$base';
END
)?>
		
		<?php echo $scripts_for_layout; ?>
		
		<title><?php echo Configure::read('Site.CMSName') ?> : <?php echo Configure::read('Site.title') ?> : <?php echo $title_for_layout; ?></title>
	</head>
	<body class="<?php echo $this->params['controller']; ?> <?php echo $this->params['action']; ?>">
		<div id="body">
			<div id="content"> 
				<?php echo $this->element('flash_messages',array('plugin'=>'core'))?>
				<?php echo $content_for_layout ?>
			</div>
		</div>
		<div class="clear"></div>
	</body>
</html>
