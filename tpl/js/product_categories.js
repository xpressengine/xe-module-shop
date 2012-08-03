function ProductCategory()
{
    this.product_category_srl = null;
    this.module_srl = null;
    this.parent_srl = 0;
    this.file_srl = null;
    this.title = null;
    this.description = null;
    this.product_count = null;
    this.friendly_url = null;
    this.include_in_navigation_menu = 'Y';
    this.regdate = null;
    this.last_update = null;
}

function fillFormWithProductCategory($category, $parent_title)
{
    jQuery("#product_category_srl").val($category.product_category_srl);
    jQuery("#parent_srl").val($category.parent_srl);

    jQuery("#filename").val($category.filename);
    jQuery("#category_image").attr("src", $category.filename);
    if($category.filename)
    {
        jQuery("#image_container").show();
    }
    else
    {
        jQuery("#image_container").hide();
    }

    jQuery("#title").val($category.title);
    jQuery("#description").val($category.description);
    jQuery("#friendly_url").val($category.friendly_url);
    jQuery("#include_in_navigation_menu").val($category.include_in_navigation_menu);
    jQuery("#regdate").val($category.regdate);
    jQuery("#last_update ").val($category.last_update);

    if($category.parent_srl !== 0)
    {
        jQuery("#parent_srl").parent().show();
    }
    else
    {
        jQuery("#parent_srl").parent().hide();
    }

    if($parent_title !== undefined)
    {
        jQuery("#parent_title").text($parent_title);
    }
}

function showProductCategoryForm()
{
    if(jQuery("#categoryFormContainer").is(":visible"))
    {
        jQuery("#categoryFormContainer").fadeOut().fadeIn();
    }
    else
    {
        jQuery("#categoryFormContainer").fadeIn();
    }
}


jQuery(document).ready(function($)
{
    $("#tree_0 a.add.root").click(function(){
        fillFormWithProductCategory(new ProductCategory());
        $("#categoryFormContainer h4").text("Add product category");
        showProductCategoryForm();
    });

    // Add behaviour
    $("#tree_0 ul a.add").click(function(){
        var $category = new ProductCategory();
        var $id = $(this).parent().attr("id");
        var $category_srl = $id.replace("tree_", "");
        var $category_title = $(this).parent().find("span:first").text().trim();
        $category.parent_srl = $category_srl;

        fillFormWithProductCategory($category, $category_title);
        $("#categoryFormContainer h4").text("Add product category");
        showProductCategoryForm();
    });

    // Edit behaviour
    $("#tree_0 ul a.modify").click(function(){
        var $id = $(this).parent().attr("id");
        var $category_srl = $id.replace("tree_", "");
        var $parent_node = $(this).parent("li").parent("ul").parent("li");
        var $parent_category_title = $parent_node.find("span:first").text().trim();

        $.exec_json('shop.procShopServiceGetProductCategory'
            , { product_category_srl : $category_srl}
            , function(data){
                if(data.error != 0)
                {
                    alert("Error " + data.error + " " + data.message);
                    return;
                }
                $category = data.product_category;
                fillFormWithProductCategory($category, $parent_category_title);
                $("#categoryFormContainer h4").text("Edit product category");
                showProductCategoryForm();
            }
        );
    });

    // Delete behaviour
    $("#tree_0 ul a.delete").click(function(){
        if(!confirm(xe.lang.confirm_delete)) return false;

        var $id = $(this).parent().attr("id");
        var $category_srl = $id.replace("tree_", "");

        $.exec_json('shop.procShopServiceDeleteProductCategory'
            , { product_category_srl : $category_srl}
            , function(data){
                if(data.error != 0)
                {
                    alert("Error " + data.error + " " + data.message);
                    return;
                }
                location.reload();
            }
        )
    });

});