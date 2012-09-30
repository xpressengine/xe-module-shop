<?php

class ShopThumbnail implements IThumbnailable
{
    private $unique_identifier;
    private $full_image_path;

    public function __construct($unique_identifier, $full_image_path)
    {
        $this->unique_identifier = $unique_identifier;
        $this->full_image_path = $full_image_path;
    }

    public function getThumbnailPath($width = 80, $height = 0, $thumbnail_type = '')
    {
        // If signiture height setting is omitted, create a square
        if(!$height) $height = $width;
        // get thumbail generation info on the doc module configuration.
        if(!in_array($thumbnail_type, array('crop','ratio'))) $thumbnail_type = 'ratio';
        // Define thumbnail information
        $thumbnail_path = sprintf('files/cache/thumbnails/%s',getNumberingPath($this->unique_identifier, 3));
        $thumbnail_file = sprintf('%s%dx%d.%s.jpg', $thumbnail_path, $width, $height, $thumbnail_type);

        // If thumbnail was already created, return path to it
        if(is_file($thumbnail_file))
        {
            return $thumbnail_file;
        }

        // Retrieve info about original image: path and extension
        $source_file = $this->full_image_path;
        $ext = pathinfo($source_file, PATHINFO_EXTENSION);

        // Create thumbnail
        $output = FileHandler::createImageFile($source_file
            , $thumbnail_file
            , $width
            , $height
            , $ext
            , $thumbnail_type);

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