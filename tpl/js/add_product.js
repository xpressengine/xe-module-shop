jQuery(document).ready(function($){
    $( ".attribute.date" ).datepicker();

    $("#categories input[type='checkbox']").change(function(){
        // Get list of visible categories
        var visible_categories = new Array();
        $("#categories input[type='checkbox']:checked").each(function(){
            visible_categories.push($(this).val());
        });

        // Show/hide rows referring to current category (the one that triggered the change event)
        var attributes = $(".attribute." + $(this).val());
        var rows = attributes.parent("div").parent("td").parent("tr");

        if($(this).is(":checked"))
        {
            rows.show();
        }
        else
        {
            $.each(rows, function(index, row){
                // Get all categories to which this attribute applies to
                categories_scope = $(row).find(".attribute").eq(0).attr("class").split(/\s+/);

                keep_visible = false;

                // Even though current category is hidden, if it is needed for other visible categories, we don't hide it
                $.each(categories_scope, function(index, category){
                    if($.inArray(category, visible_categories) != -1)
                    {
                        keep_visible = true;
                        return;
                    }
                });

                if(!keep_visible)
                    $(row).hide();

            });
        }
    });
});