/**
 * Toggles categories in category menu
 * @author: Daniel Ionescu
 */
jQuery(document).ready(function($) {

    // Display selected category parent list
    function showPath(item) {

        // Cycle through all parents and display all lists
        if (!item.parent().hasClass('body-left-panel')) {

            if (item.parent().prop("tagName").toLowerCase()=='ul') {

                item.parent().show();

            }

            showPath(item.parent());

        }

        // If item has children also display those
        item.children('ul').show();

        return false;

    }
    showPath($('.body-left-panel li.active'));

    // Opens a sublist
    $('span.open-sign').click(function() {

        $(this).parent().next('ul').show(400);
        $(this).next().show();
        $(this).hide();

    });

    // Closes a sublist
    $('span.close-sign').click(function() {

        $(this).parent().next('ul').hide(400);
        $(this).prev().show();
        $(this).hide();

    });

});
