<?php
/**
 * The template for displaying meeting details of zoom
 *
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/fragments/meeting-details.php.
 *
 * @author      Deepen Bajracharya (CodeManas)
 * @created     3.0.0
 * @updated     3.6.0
 */

global $zoom;

if ( ! vczapi_pro_version_active() && vczapi_pro_check_type( $zoom['api']->type ) || empty( $zoom ) ) {
	return;
}
?>
<div class="dpn-zvc-sidebar-box">
    <div class="dpn-zvc-sidebar-tile">
        <h3><?php _e( 'Details', 'video-conferencing-with-zoom-api' ); ?></h3>
    </div>
    <div class="dpn-zvc-sidebar-content">
        <div class="dpn-zvc-sidebar-content-list">
            <span><strong><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?>:</strong></span> <span><?php the_title(); ?></span>
        </div>
        <div class="dpn-zvc-sidebar-content-list">
            <span><strong><?php _e( 'Hosted By', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
            <span><?php echo esc_html( $zoom['host_name'] ); ?></span>
        </div>
		<?php if ( ! empty( $zoom['api']->start_time ) ) { ?>
            <div class="dpn-zvc-sidebar-content-list">
                <span><strong><?php _e( 'Start', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                <span class="sidebar-start-time"><?php echo vczapi_dateConverter( $zoom['api']->start_time, $zoom['api']->timezone, 'F j, Y @ g:i a' ); ?></span>
            </div>
		<?php } ?>
		<?php if ( ! empty( $zoom['terms'] ) ) { ?>
            <div class="dpn-zvc-sidebar-content-list">
                <span><strong><?php _e( 'Category', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                <span class="sidebar-category"><?php echo implode( ', ', $zoom['terms'] ); ?></span>
            </div>
		<?php } ?>
		<?php if ( ! empty( $zoom['api']->duration ) ) { ?>
            <div class="dpn-zvc-sidebar-content-list">
                <span><strong><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                <span><?php echo $zoom['api']->duration; ?></span>
            </div>
		<?php } ?> <?php if ( ! empty( $zoom['api']->timezone ) ) { ?>
            <div class="dpn-zvc-sidebar-content-list">
                <span><strong><?php _e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?>:</strong></span>
                <span><?php echo $zoom['api']->timezone; ?></span>
            </div>
		<?php } ?>
        <p class="dpn-zvc-display-or-hide-localtimezone-notice"><?php printf( __( '%sNote%s: Countdown time is shown based on your local timezone.', 'video-conferencing-with-zoom-api' ), '<strong>', '</strong>' ); ?></p>
    </div>
</div>
