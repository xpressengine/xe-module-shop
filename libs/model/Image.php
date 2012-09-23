<?php
class Image extends BaseItem
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
        return "./files/attach/images/shop/$this->module_srl/product-images/$this->product_srl/$this->filename";
    }

}