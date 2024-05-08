<?php /* Template Name: em-page-template */ ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php edit_post_link(); ?>
<?php the_post(); ?>
<?php the_content(); ?>
<?php wp_footer(); ?>
</body>
</html>