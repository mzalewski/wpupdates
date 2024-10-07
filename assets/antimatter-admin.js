(function($) { 

    // hook wp-plugin-installing on document
    $(document).on('wp-plugin-installing', function(e, data) {
        var slug = data.slug;
        var t = $(".plugin-card-" + slug).find('.updating-message');
        var store = $(t).attr('data-store');
        if (store) { 
            data.store = store;
        }
    });

})(jQuery);