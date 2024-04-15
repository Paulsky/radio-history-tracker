<?php
if ( ! isset( $tracks_query ) || ! $tracks_query->have_posts() ) {
	return;
}
?>
<ul class="most-played-tracks">
	<?php while ( $tracks_query->have_posts() ): $tracks_query->the_post(); ?>
		<?php
		$played_count  = get_post_meta( get_the_ID(), 'rht_track_played_count', true );
		$artists       = wp_get_post_terms( get_the_ID(), 'rht_artist', array( "fields" => "names" ) );
		$artist_names  = ! empty( $artists ) ? implode( ', ', $artists ) : '';
		$has_thumbnail = has_post_thumbnail();
		?>
        <li class="track-item <?php echo $has_thumbnail ? 'has-artwork' : 'no-artwork'; ?>">
            <div class="track-wrapper">
                <div class="track-image">
					<?php if ( $has_thumbnail ) {
						echo get_the_post_thumbnail( get_the_ID(), 'medium' );
					} ?></div>
                <div class="track-title"><?php echo esc_html( get_the_title() ); ?></div>
                <div class="track-artist"><?php echo esc_html( $artist_names ); ?></div>
                <div class="track-played-count"><?php echo esc_html( $played_count ); ?></div>
            </div>
        </li>
	<?php endwhile; ?>
</ul>