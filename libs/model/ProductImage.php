<?php
class ProductImage extends BaseItem
{
    public
        $image_srl,
        $product_srl,
        $module_srl,
        $member_srl,
        $filename,
        $is_primary,
        $file_size,
        $regdate,
		$source_filename;

    public function getFullPath()
    {
        $image_path = "./files/attach/images/shop/$this->module_srl/product-images/$this->product_srl/$this->filename";
        if(is_file($image_path))
        {
            return $image_path;
        }
        return "./files/attach/shop/".getNumberingPath($this->module_srl,3)."/img/missingProduct.png";
    }

    public function getThumbnailPath($width = 80, $height = 0, $thumbnail_type = '')
    {
        // If signiture height setting is omitted, create a square
        if(!$height) $height = $width;
        // get thumbail generation info on the doc module configuration.
        if(!in_array($thumbnail_type, array('crop','ratio'))) $thumbnail_type = 'ratio';
        // Define thumbnail information
        $thumbnail_path = sprintf('files/cache/thumbnails/%s',getNumberingPath($this->image_srl, 3));
        $thumbnail_file = sprintf('%s%dx%d.%s.jpg', $thumbnail_path, $width, $height, $thumbnail_type);

        // If thumbnail was already created, return path to it
        if(is_file($thumbnail_file))
        {
            return $thumbnail_file;
        }

        // Retrieve info about original image: path and extension
        $source_file = $this->getFullPath();
        $ext = pathinfo($source_file, PATHINFO_EXTENSION);

        // Create thumbnail
        $output = FileHandler::createImageFile($source_file
                                        , $thumbnail_file
                                        , $width
                                        , $height
                                        , $ext
                                        , $thumbnail_type) ;

        if($output)
        {
            return $thumbnail_file;
        }
        else
        {
            return '';
        }
    }

}