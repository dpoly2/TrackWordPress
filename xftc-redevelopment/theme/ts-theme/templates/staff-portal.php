<?php
/**
 * Template Name: Staff/Coach Portal
 * @package TRACKSUITE_Theme
 */
TRACKSUITE_partial( 'header' );
?>

<div class="portal-header">
    <div class="container">
        <p class="section-header__eyebrow" style="margin-bottom:.25rem">🏟️ Staff Area</p>
        <h1 class="portal-header__title">Coach &amp; Staff Portal</h1>
    </div>
</div>

<section class="section" style="padding-top:1.5rem;">
    <div class="container">
        <?php echo do_shortcode( '[TRACKSUITE_staff_portal]' ); ?>
    </div>
</section>

<?php TRACKSUITE_partial( 'footer' ); ?>
