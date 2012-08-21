<?php

require_once dirname(__FILE__) . '/../model/Image.php';
require_once "BaseRepository.php";

/**
 * Handles database operations for Image
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class ImageRepository extends BaseRepository
{
	/**
	 * Insert a new image; returns the ID of the newly created record
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $image Image
	 * @return int
	 */
	public function insertImage(Image &$image)
	{
        if ($image->image_srl) throw new Exception('A srl must NOT be specified');
        $image->image_srl = getNextSequence();
		if($image->file_size > 0){
			$output = executeQuery('shop.insertImage', $image);
			$this->saveImage($image);
			//if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
			return $output;
		}
		else return;
	}

	/**
	 * Save image to disk
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $image Image
	 * @return boolean
	 */
	public function saveImage(Image &$image)
	{
		try{
			$path = sprintf('./files/attach/images/shop/%d/product-images/%d/', $image->module_srl , $image->product_srl);
			$filename = sprintf('%s%s', $path, $image->filename);
			FileHandler::copyFile($image->source_filename, $filename);
		}
		catch(Exception $e)
		{
			return new Object(-1, $e->getMessage());
		}

		return TRUE;;

	}

    /**
     * Update an image
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $image Image
     * @throws Exception
     * @return mixed
     */
    public function updateImage(Image $image)
    {

    }

    /**
	 * Deletes one or more images by $image_srl or $module_srl
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $args array
	 */
	public function deleteImages($args)
	{

	}

    /**
     * Retrieve a list of Images object from the database by product_srl
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $product_srl int
     * @return Image list
     */
    public function getImagesList($product_srl)
    {

    }

	/**
	 * Retrieve a Images object from the database by image_srls
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $image_srls array
	 * @return Image list
	 */
	public function getImages($image_srls)
	{
		$args = new stdClass();
		$args->image_srls = $image_srls;
		$output = executeQuery('shop.getProductImages', $args);
		if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
		return $output->data;
	}


	/**
	 * Create Image list from uploaded files
	 * @author Dan Dragan (dev@xpressengine.org)
	 * $params array $files
	 * @return array $images
	 */
	public function createImagesUploadedFiles(Array $files)
	{
		$args = new stdClass();
		foreach($files as $file){
			$args->source_filename = $file['tmp_name'];
			$args->filename = $file['name'];
			$args->file_size = $file['size'];
			$image = new Image($args);
			$images[] = $image;
		}
		return $images;
	}
}