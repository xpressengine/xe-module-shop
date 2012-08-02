function ProductCategory()
{
    this.$product_category_srl = null;
    this.$module_srl = null;
    this.$parent_srl = 0;
    this.$file_srl = null;
    this.$title = null;
    this.$description = null;
    this.$product_count = null;
    this.$friendly_url = null;
    this.$include_in_navigation_menu = 'Y';
    this.$regdate = null;
    this.$last_update = null;
}

function fillFormWithProductCategory($category, $parent_title)
{
    jQuery("#title").val($category.$title);
    jQuery("#product_category_srl").val($category.$product_category_srl);
    jQuery("#parent_srl").val($category.$parent_srl);

    if($category.$parent_srl !== 0)
    {
        jQuery("#parent_srl").parent().fadeOut().fadeIn();
    }
    else
    {
        jQuery("#parent_srl").parent().fadeOut();
    }

    if($parent_title !== undefined)
    {
        jQuery("#parent_title").text($parent_title);
    }
}


jQuery(document).ready(function($)
{
    $("#tree_0 a.add.root").click(function(){
        fillFormWithProductCategory(new ProductCategory());
        $("#categoryFormContainer").show();
    });

    $("#tree_0 ul a.add").click(function(){
        var $category = new ProductCategory();
        var $id = $(this).parent().attr("id");
        var $category_srl = $id.replace("tree_", "");
        var $category_title = $(this).parent().find("span:first").text().trim();
        $category.$parent_srl = $category_srl;

        fillFormWithProductCategory($category, $category_title);
        $("#categoryFormContainer").show();
    });

});