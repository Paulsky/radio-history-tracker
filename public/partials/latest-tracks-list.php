<?php
if ( ! isset( $tracks_query ) || ! $tracks_query->have_posts() ) {
	return;
}
?>
<ul class="latest-tracks">
	<?php while ( $tracks_query->have_posts() ): $tracks_query->the_post(); ?>
		<?php
		$played_at    = get_post_meta( get_the_ID(), 'rht_track_latest_played_at', true );
		$artists      = wp_get_post_terms( get_the_ID(), 'rht_artist', array( "fields" => "names" ) );
		$artist_names = ! empty( $artists ) ? implode( ', ', $artists ) : '';
		$has_thumbnail = has_post_thumbnail();
		?>
        <li class="track-item <?php echo $has_thumbnail ? 'has-artwork' : 'no-artwork'; ?>">
            <div class="track-image">
				<?php if ( $has_thumbnail ) {
					echo get_the_post_thumbnail( get_the_ID(), 'medium' );
				} ?></div>
            <div class="track-title"><?php echo esc_html( get_the_title() ); ?></div>
            <div class="track-artist"><?php echo esc_html( $artist_names ); ?></div>
            <div class="track-played-at"><?php echo date_i18n( 'H:i', intval( $played_at ) ); ?></div>
        </li>
	<?php endwhile; ?>
</ul>