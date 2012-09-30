<?php

/**
 * Interface for objects that can generate a thumbnail
 */
interface IThumbnailable
{
    function getThumbnailPath($width = 80, $height = 0, $thumbnail_type = '');
}