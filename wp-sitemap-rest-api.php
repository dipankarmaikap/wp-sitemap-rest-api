<?php

/**
 * Plugin Name: WP Sitemap Rest Api
 * Description: Generating rest api sitemap for your headless wordpress site.
 * Version: 0.1.3
 * Author:      Dipankar Maikap
 * Author URI:  https://dipankarmaikap.com/
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 */

function wsra_get_user_inputs()
{
    $pageNo = isset($_GET['pageNo']) ? intval($_GET['pageNo']) : 1;
    $perPage = isset($_GET['perPage']) ? intval($_GET['perPage']) : 100;
    $taxonomy = isset($_GET['taxonomyType']) ? sanitize_text_field($_GET['taxonomyType']) : '';
    $postType = isset($_GET['postType']) ? sanitize_text_field($_GET['postType']) : 'post';

    $paged = $pageNo ? $pageNo : 1;
    $perPage = $perPage ? $perPage : 100;
    $offset = ($paged - 1) * $perPage;

    $args = array(
        'number' => $perPage,
        'offset' => $offset,
    );

    $postArgs = array(
        'posts_per_page' => $perPage,
        'post_type' => $postType,
        'paged' => $paged,
    );

    return [$args, $postArgs, $taxonomy];
}

function wsra_generate_author_api()
{
    [$args] = wsra_get_user_inputs();
    $author_urls = array();
    $authors =  get_users($args);
    foreach ($authors as $author) {
        $fullUrl = esc_url(get_author_posts_url($author->ID));
        $url = str_replace(home_url(), '', $fullUrl);
        $tempArray = [
            'url' => $url,
        ];
        array_push($author_urls, $tempArray);
    }
    return array_merge($author_urls);
}
function wsra_generate_taxonomy_api()
{
    [$args,, $taxonomy] = wsra_get_user_inputs();
    $taxonomy_urls = array();

    // Fetch terms for the specified taxonomy
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'number' => $args['number'],
        'offset' => $args['offset'],
    ));

    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $fullUrl = esc_url(get_term_link($term->term_id, $taxonomy));
            $url = str_replace(home_url(), '', $fullUrl);
            $tempArray = [
                'url' => $url,
            ];
            array_push($taxonomy_urls, $tempArray);
        }
    }

    return $taxonomy_urls;
}

function wsra_generate_posts_api()
{
    [, $postArgs] = wsra_get_user_inputs();
    $postUrls = array();
    $query = new WP_Query($postArgs);

    while ($query->have_posts()) {
        $query->the_post();
        $uri = str_replace(home_url(), '', get_permalink());
        $tempArray = [
            'url' => $uri,
            'post_modified_date' => get_the_modified_date(),
        ];
        array_push($postUrls, $tempArray);
    }
    wp_reset_postdata();
    return array_merge($postUrls);
}
function wsra_generate_totalpages_api()
{
    $args = array(
        'exclude_from_search' => false
    );
    $argsTwo = array(
        'publicly_queryable' => true
    );
    $post_types = get_post_types($args, 'names');
    $post_typesTwo = get_post_types($argsTwo, 'names');
    $post_types = array_merge($post_types, $post_typesTwo);
    unset($post_types['attachment']);
    $defaultArray = [
        'category' => count(get_categories()),
        'tag' => count(get_tags()),
        'user' => (int)count_users()['total_users'],
    ];
    $tempValueHolder = array();
    foreach ($post_types as $postType) {
        $tempValueHolder[$postType] = (int)wp_count_posts($postType)->publish;
    }
    // Fetch all custom taxonomies
    $custom_taxonomies = get_taxonomies(['_builtin' => false], 'names');
    foreach ($custom_taxonomies as $taxonomy) {
        if (taxonomy_exists($taxonomy)) {
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ]);
            $tempValueHolder[$taxonomy] = count($terms);
        }
    }

    return array_merge($defaultArray, $tempValueHolder);
}

add_action('rest_api_init', function () {
    register_rest_route('sitemap/v1', '/posts', array(
        'methods' => 'GET',
        'callback' => 'wsra_generate_posts_api',
    ));
});
add_action('rest_api_init', function () {
    register_rest_route('sitemap/v1', '/taxonomy', array(
        'methods' => 'GET',
        'callback' => 'wsra_generate_taxonomy_api',
    ));
});
add_action('rest_api_init', function () {
    register_rest_route('sitemap/v1', '/author', array(
        'methods' => 'GET',
        'callback' => 'wsra_generate_author_api',
    ));
});
add_action('rest_api_init', function () {
    register_rest_route('sitemap/v1', '/totalpages', array(
        'methods' => 'GET',
        'callback' => 'wsra_generate_totalpages_api',
    ));
});
