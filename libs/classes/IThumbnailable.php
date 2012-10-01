<?php

/**
 * Interface for objects that can generate a thumbnail
 */
interface IThumbnailable
{
    /**
     * Path to generated thumbnail
     *
     * @param int $width
     * @param int $height
     * @param string $thumbnail_type
     * @return mixed
     */
    function getThumbnailPath($width = 80, $height = 0, $thumbnail_type = '');
}