<?php

// Hook into WordPress dashboard setup to remove and add the widget
add_action('wp_dashboard_setup', 'antimatter_wp_custom_dashboard_widgets');



function antimatter_wp_custom_dashboard_widgets() {
    $custom_feed_url = defined('ANTIMATTER_WP_CUSTOM_FEED') ? ANTIMATTER_WP_CUSTOM_FEED : get_option('antimatter_wp_custom_feed');
    
    if (empty($custom_feed_url)) {
        return;
    }
    
    // Remove the default "At a Glance" and "WordPress News" widgets
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
    $widget_title = defined('ANTIMATTER_WP_WIDGET_TITLE') ? ANTIMATTER_WP_WIDGET_TITLE : get_option('antimatter_wp_widget_title', 'Your Custom News Feed');

    // Add custom widget to replace the news section
    wp_add_dashboard_widget(
        'antimatter_wp_custom_news_widget', // Widget slug
        $widget_title,            // Widget title
        'antimatter_wp_render_custom_news_widget', // Function to display the content
        'side'
    );
}

// Function to display the custom news content
function antimatter_wp_render_custom_news_widget() {
    
    $custom_feed_url = defined('ANTIMATTER_WP_CUSTOM_FEED') ? ANTIMATTER_WP_CUSTOM_FEED : get_option('antimatter_wp_custom_feed');
    $widget_heading = defined('ANTIMATTER_WP_WIDGET_HEADING') ? ANTIMATTER_WP_WIDGET_HEADING : get_option('antimatter_wp_widget_heading', 'Custom WordPress News');
    

    // Replace this with the custom feed or content you'd like to show
    echo '<div class="custom-news-widget">';
    echo '<h4>' . esc_html($widget_heading) . '</h4>';
    
  
    
    
    // Example of pulling a custom RSS feed (replace with your feed URL)
    $rss = fetch_feed($custom_feed_url); // Replace with your custom RSS feed URL
    $max_items = 0;
    if (!is_wp_error($rss)) {
        $max_items = $rss->get_item_quantity(5); // Number of items to display
        $rss_items = $rss->get_items(0, $max_items);
    }

    if ($max_items == 0) {
        echo '<p>No news items found.</p>';
    } else {
        echo '<ul>';
        foreach ($rss_items as $item) {
            echo '<li>';
            echo '<a href="' . esc_url($item->get_permalink()) . '" target="_blank" title="' . esc_html($item->get_title()) . '">' . esc_html($item->get_title()) . '</a>';
            echo '</li>';
        }
        echo '</ul>';
    }
    
    echo '</div>';
}