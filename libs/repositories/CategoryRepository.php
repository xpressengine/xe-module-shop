<?php

/**
 * Handles database operations for Product Category
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class CategoryRepository extends BaseRepository
{
	private $category_images_folder = './files/attach/images/shop/%d/product-categories/';

	/**
	 * Insert a new Product category; returns the ID of the newly created record.
	 *
	 * @param Category $category Category to inserted
	 *
	 * @throws Exception DatabaseError.
	 * @return category_srl int
	 */
	public function insertCategory(Category $category)
	{
		$category->category_srl = getNextSequence();
		$output = executeQuery('shop.insertCategory', $category);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
		return $category->category_srl;
	}

	/**
	 * Deletes a product category by $category_srl or $module_srl
	 *
	 * @param stdClass $args Can have the following properties: category_srl or module_srl
	 *
	 * @throws Exception
	 * @return bool
	 */
	public function deleteCategory($args)
	{
		if(!isset($args->category_srl) && !isset($args->module_srl))
		{
			throw new Exception("Missing arguments for Product category delete: please provide [category_srl] or [module_srl]");
		}

		// Get category info before deleting it, so we can also delete category image
		if($args->category_srl)
		{
			$category = $this->getCategory($args->category_srl);
		}

		$output = executeQuery('shop.deleteCategory', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		$output = executeQuery('shop.deleteAttributesScope', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		$output = executeQuery('shop.deleteProductCategories', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		if($args->category_srl)
		{
			$output = $this->deleteCategoryImage($category->filename);
			if(!$output)
			{
				throw new Exception("Could not delete category image");
			}
		}
		else
		{
			$this->deleteCategoriesImages($args->module_srl);
		}

		return TRUE;
	}

	/**
	 * Retrieve a Category object from the database given a srl
	 *
	 * @param int $category_srl by which to select the Category
	 *
	 * @throws Exception
	 * @return Category
	 */
	public function getCategory($category_srl)
	{
		$args = new stdClass();
		$args->category_srl = $category_srl;

		$output = executeQuery('shop.getCategory', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		$category = new Category($output->data);

		return $category;
	}

	/**
	 * Update a product category
	 *
	 * @param Category $category Object to be persisted
	 *
	 * @throws Exception
	 * @return boolean
	 */
	public function updateCategory(Category $category)
	{
		$output = executeQuery('shop.updateCategory', $category);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
		return TRUE;
	}

	/**
	 * Get all product categories for a module as a tree
	 * Returns root node
	 *
	 * @param int $module_srl Module for which to get all categories as a tree
	 *
	 * @throws Exception
	 * @return CategoryTreeNode Tree root node
	 */
	public function getCategoriesTree($module_srl)
	{
		$args = new stdClass();
		$args->module_srl = $module_srl;

		// Retrieve categories from database
		$output = executeQueryArray('shop.getCategories', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		// Arrange hierarchically
		$nodes = array();
		$nodes[0] = new CategoryTreeNode();
		foreach($output->data as $pc)
		{
			$nodes[$pc->category_srl] = new CategoryTreeNode(new Category($pc));
            if(isset($nodes[$pc->parent_srl]))
            {
                $nodes[$pc->parent_srl]->addChild($nodes[$pc->category_srl]);
            }
		}

		return $nodes[0];
	}

    /**
     * Add categories info to export folder
     * @author Dan Dragan (dev@xpressengine.org)
     *
     * @param array $categories
     *
     * @return boolean
     */
    public function addCategoriesToExportFolder($categories)
    {
        $buff = '';
        //table header for categories csv
        foreach($categories[0]->category as $key => $value)
        {
            if(!in_array($key,array('member_srl','module_srl','regdate','last_update','repo','product_count')))
            {
                if($key == 'category_srl') $buff = $buff.'id,';
                else $buff = $buff.$key.",";
            }
        }
        $buff = $buff."include_in_navigation_menu\r\n";
        //table values  for categories csv
        foreach($categories as $category){
            // add image to temp folder
            $filename = $category->category->filename;
            $export_filename = sprintf('./files/attach/shop/export-import/images/%s',basename($category->category->filename));
            FileHandler::copyFile($filename,$export_filename);

            foreach($category->category as $key => $value){
                if(!in_array($key,array('member_srl','module_srl','regdate','last_update','repo','product_count','filename')))
                {
                    $buff = $buff.$value.",";
                }
                if($key == 'filename'){
                    $buff = $buff.basename($value).",";
                }
            }
            $buff = $buff.$category->category->include_in_navigation_menu."\r\n";
        }
        $category_csv_filename = 'categories.csv';
        $category_csv_path = sprintf('./files/attach/shop/export-import/%s', $category_csv_filename);
        FileHandler::writeFile($category_csv_path, $buff);

        return TRUE;
    }

    /**
     * import categories from import folder
     * @author Dan Dragan (dev@xpressengine.org)
     *
     * @param $args for module_srl
     *
     * @return  $category_ids correlation
     */
    public function insertCategoriesFromImportFolder($params)
    {
        $csvString = file_get_contents('./files/attach/shop/export-import/categories.csv');
        $csvData = str_getcsv($csvString, "\n");
        $keys = explode(',',$csvData[0]);

        foreach ($csvData as $idx=>$csvLine){
            if($idx != 0){
                $cat = explode(',',$csvLine);
                foreach($cat as $key=>$value){
                    if($keys[$key] != ''){
                        $args[$keys[$key]] = $value;
                    }
                }
                $args = (object) $args;
                $categories[] = $args;
                unset($args);
            }
        }
        $category_ids = new ArrayObject();
        foreach($categories as $category){
            $cat = new Category($category);
            $cat->filename = $this->saveCategoryImage($params->module_srl, $cat->filename,'./files/attach/shop/export-import/images/'.$cat->filename);
            $cat->module_srl = $params->module_srl;
            if($cat->parent_srl){
               $cat->parent_srl = $category_ids[$cat->parent_srl];
            }
            $cat->category_srl = $this->insertCategory($cat);
            $category_ids[$category->id] = $cat->category_srl;
            $oCategories[] = $cat;
        }
        return $category_ids;
    }

	/**
	 * Save category image to disc
	 *
	 * @param int    $module_srl        Module's srl
	 * @param string $original_filename Original filename of the uploaded file
	 * @param string $tmp_name          Uploaded file's content
	 *
	 * @return string
	 */
	public function saveCategoryImage($module_srl, $original_filename, $tmp_name)
	{
		$tmp_arr = explode('.', $original_filename);
		$extension = $tmp_arr[count($tmp_arr) - 1];

		$path = sprintf($this->category_images_folder, $module_srl);
		$filename = sprintf('%s%s.%s', $path, uniqid('product-category-'), $extension);
		FileHandler::copyFile($tmp_name, $filename);

		return $filename;
	}

	/**
	 * Delete category image from disc
	 *
	 * @param string $filename Name of the file to delete (category image)
	 *
	 * @return boolean
	 */
	public function deleteCategoryImage($filename)
	{
		if(!$filename)
		{
			return TRUE;
		}

		return FileHandler::removeFile($filename);
	}

	/**
	 * Deletes all category images for a given module
	 *
	 * @param int $module_srl Module
	 *
	 * @return void
	 */
	public function deleteCategoriesImages($module_srl)
	{
		FileHandler::removeFilesInDir(sprintf($this->category_images_folder, $module_srl));
	}

	/**
	 * Returns array of all parent categories
	 *
	 * @param Category $category Current category
	 *
	 * @return Category[]
	 */
	public function getCategoryParents(Category $category)
	{
		$parents = array();

		if($category->parent_srl == 0)
		{
			return $parents;
		}

		$parent_category = $this->getCategory($category->parent_srl);
		$parents[] = $parent_category;
		$rest_of_parents = $this->getCategoryParents($parent_category);

		return array_merge($parents, $rest_of_parents);
	}

}
/* End of file CategoryRepository.php */
/* Location: ./modules/shop/libs/repositories/CategoryRepository.php */
