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
    $pageNo = sprintf("%d", isset($_GET['pageNo']) ? $_GET['pageNo'] : 1);
    $perPage = sprintf("%d", isset($_GET['perPage']) ? $_GET['perPage'] : 10);
    $taxonomy = isset($_GET['taxonomyType']) ? $_GET['taxonomyType'] : '';
    $postType = isset($_GET['postType']) ? $_GET['postType'] : '';
    $paged = $pageNo ? $pageNo : 1;
    $perPage = $perPage ? $perPage : 100;
    $offset = ($paged - 1) * $perPage;
    $args = array(
        'number' => $perPage,
        'offset' => $offset,
    );
    $postArgs = array(
        'posts_per_page' => $perPage,
        'post_type' => strval($postType ? $postType : 'post'),
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
    $taxonomys = $taxonomy == 'tag' ? get_tags($args) : get_categories($args);
    foreach ($taxonomys as $taxonomy) {
        $fullUrl = esc_url(get_category_link($taxonomy->term_id));
        $url = str_replace(home_url(), '', $fullUrl);
        $tempArray = [
            'url' => $url,
        ];
        array_push($taxonomy_urls, $tempArray);
    }
    return array_merge($taxonomy_urls);
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
