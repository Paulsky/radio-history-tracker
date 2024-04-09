<?php

class Track_Meta_Boxes {
	private $config;
	protected $plugin_name;
	protected $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->init_config();
		$this->process_cpts();
	}

	private function process_cpts() {
		if ( ! empty( $this->config['cpt'] ) ) {
			if ( empty( $this->config['post-type'] ) ) {
				$this->config['post-type'] = [];
			}
			$parts                     = explode( ',', $this->config['cpt'] );
			$parts                     = array_map( 'trim', $parts );
			$this->config['post-type'] = array_merge( $this->config['post-type'], $parts );
		}
	}

	private function init_config() {
		$this->config = [
			"title"    => __( "Track details", "radio-ht" ),
			"prefix"   => "rht_track_",
			"domain"   => "radio-ht",
			"context"  => "normal",
			"priority" => "high",
			"cpt"      => "rht_track",
			"fields"   => [
				[
					"type"        => "datetime-array",
					"label"       => __( "Played at", "radio-ht" ),
					"id"          => "rht_track_played_at_timestamps",
					"description" => __( "Add played at. Format: YYYY-MM-DDThh:mm", "radio-ht" )
				],
				[
					"type"  => "readonly",
					"label" => __( "Most recent played at", "radio-ht" ),
					"id"    => "rht_track_latest_played_at"
				],
				[
					"type"  => "readonly",
					"label" => __( "Total plays", "radio-ht" ),
					"id"    => "rht_track_played_count"
				],
				[
					"type"  => "readonly",
					"label" => __( "Track slug", "radio-ht" ),
					"id"    => "rht_track_slug"
				],
				[
					"type"  => "readonly",
					"label" => __( "Radio stream", "radio-ht" ),
					"id"    => "rht_track_radio_stream_id"
				]
			]
		];
	}

	public function add_meta_boxes() {
		foreach ( $this->config['post-type'] as $screen ) {
			add_meta_box(
				sanitize_title( $this->config['title'] ),
				__( $this->config['title'], $this->config['domain'] ),
				[ $this, 'add_meta_box_callback' ],
				$screen,
				$this->config['context'],
				$this->config['priority']
			);
		}
	}

	public function save_post( $post_id ) {
		if ( ! isset( $_POST['rht_track_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['rht_track_meta_box_nonce'], 'rht_track_save_meta_box_data' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( isset( $_POST['post_type'] ) && 'rht_track' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		}

		foreach ( $this->config['fields'] as $field ) {
			if ( $field['type'] === 'readonly' ) {
				continue;
			}

			if ( isset( $_POST[ $field['id'] ] ) ) {

				$value           = $_POST[ $field['id'] ];
				$sanitized_value = [];

				switch ( $field['type'] ) {
					case 'text':
						$sanitized_value = sanitize_text_field( $value );
						break;
					case 'datetime-array':
						foreach ( $value as $dateTime ) {
							if ( ! empty( $dateTime ) ) {
								//TO UNIX timestamp
								$timestamp = strtotime( $dateTime );
								if ( $timestamp ) {
									$sanitized_value[] = $timestamp;
								}
							}
						}
						break;
					default:
						$sanitized_value = sanitize_text_field( $value );
				}

				update_post_meta( $post_id, $field['id'], $sanitized_value );

			}
		}
	}

	public function add_meta_box_callback( $post ) {
		wp_nonce_field( 'rht_track_save_meta_box_data', 'rht_track_meta_box_nonce' );
		$this->fields_table( $post );
	}

	private function fields_table( $post ) {
		?>
        <table class="form-table" role="presentation">
            <tbody>
			<?php foreach ( $this->config['fields'] as $field ): ?>
                <tr>
                    <th scope="row"><?php echo esc_html( $field['label'] ); ?></th>
                    <td><?php $this->field( $field, $post ); ?></td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
		<?php
	}

	private function field( $field, $post ) {
		$value = get_post_meta( $post->ID, $field['id'], true );
		?>
		<?php switch ( $field['type'] ): case 'text': ?>
            <input type="text" id="<?= esc_attr( $field['id'] ); ?>" name="<?= esc_attr( $field['id'] ); ?>"
                   value="<?= esc_attr( $value ); ?>" class="regular-text"/>
			<?php if ( isset( $field['help'] ) ): ?>
                <div class="rht-help"><?= $field['help']; ?></div>
			<?php endif; ?>
			<?php break; ?>
		<?php case 'datetime-array':
			$timestamps = get_post_meta( $post->ID, $field['id'], true ) ?: [ '' ]; // empty one is necessary
			?>
            <div class="timestamps-wrapper">
				<?php foreach ( $timestamps as $timestamp ):
					$dateTimeValue = $timestamp ? date( 'Y-m-d\TH:i', $timestamp ) : '';
					?>
                    <div class="timestamp-wrapper">
                        <input type="datetime-local" name="<?= esc_attr( $field['id'] ); ?>[]"
                               value="<?= esc_attr( $dateTimeValue ); ?>" class="regular-text"/>
                        <a href="#" class="remove-timestamp button"><?= __( "Remove", "radio-ht" ); ?></a>
                    </div>
				<?php endforeach; ?>
            </div>
            <a href="#" id="add-timestamp" class="button"><?= __( "Add timestamp", "radio-ht" ); ?></a>
			<?php break; ?>
		<?php case 'readonly': ?>
			<?php
			if ( $field['id'] === 'rht_track_latest_played_at' ) {
				$value = $value ? date( 'Y-m-d H:i:s', $value ) : '';
			} elseif ( $field['id'] === 'rht_track_radio_stream_id' ) {
				if ( $value ) {
					$value = get_the_title( $value );
				} else {
					$value = '';
				}
			}
			?>
            <input type="text" readonly="readonly" value="<?= esc_attr( $value ); ?>" class="regular-text"/>
			<?php break; ?>
		<?php endswitch; ?>
		<?php
	}
}
