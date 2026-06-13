<?php
/*
 * Template Name: Spécial - Ouvrir le 1e enfant
 */

$children = get_posts([
  'post_type'   => 'page',
  'post_parent' => get_the_ID(),
  'orderby'     => 'menu_order',
  'order'       => 'asc',
]);

wp_redirect(get_the_permalink($children[0]->ID));
