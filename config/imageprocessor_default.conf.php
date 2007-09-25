<?php

/**
 * Set up the ImageProcessor service for generating thumbnails
 *
 * USAGE: Copy this file to imageprocessor.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: imageprocessor_default.conf.php,v 1.4 2007/09/25 19:46:17 adamfranco Exp $
 */
 
// :: Set up the ImageProcessor service for generating thumbnails ::
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('thumbnail_format', "image/jpeg");
	$configuration->addProperty('use_gd', FALSE);
	$configuration->addProperty('gd_formats', array());
	$configuration->addProperty('use_imagemagick', TRUE);
	$configuration->addProperty('imagemagick_path', "/usr/local/bin");
	$configuration->addProperty('imagemagick_temp_dir', "/tmp");
	$configuration->addProperty('imagemagick_formats', array());
	
	Services::startManagerAsService("ImageProcessingManager", $context, $configuration);