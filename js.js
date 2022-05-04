
    jQuery(document).ready(function(){

    //this script here will allow for search of the workshop name
    jQuery("#garden_search").on("keyup", function() {
        var value = jQuery(this).val().toLowerCase();
        jQuery("#garden_tea tr").filter(function() {
            jQuery(this).toggle(jQuery(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

});
