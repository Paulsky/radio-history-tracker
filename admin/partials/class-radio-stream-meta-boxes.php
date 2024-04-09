<?php

class Radio_Stream_Meta_Boxes {
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
			"title"    => __( "Stream details", "radio-ht" ),
			"prefix"   => "rht_stream_",
			"domain"   => "radio-ht",
			"context"  => "normal",
			"priority" => "high",
			"cpt"      => "rht_radio_stream",
			"fields"   => [
				[
					"type"    => "select",
					"label"   => __( "Stream type", "radio-ht" ),
					"options" => [
						"shoutcast" => __( "Shoutcast", "radio-ht" ),
					],
					"id"      => "rht_stream_type"
				],
				[
					"type"    => "select",
					"label"   => __( "Stream metadata type", "radio-ht" ),
					"options" => [
						"xml"    => __( "XML", "radio-ht" ),
						"json"   => __( "JSON", "radio-ht" ),
						"html"   => __( "HTML", "radio-ht" ),
						"stream" => __( "Stream", "radio-ht" ),
					],
					"id"      => "rht_stream_metadata_type",
					"help"    => __( "<ul><li>XML: Endpoint with XML containing a &lt;SONGHISTORY&gt;&lt;SONG&gt;&lt;/SONG&gt;&lt;/SONGHISTORY&gt; structure. Example: /stats?sid=1, /played?type=xml of /admin.cgi?sid=1&mode=viewxml&page=4 (with username and password)</li><li>JSON: Endpoint with JSON format data for track history. Example: /stats?sid=1&json=1 or /played?type=json</li><li>HTML: Endpoint with HTML containing an HTML table with track history. Example: /played</li><li>Stream: The stream endpoint itself. It will get the current playing track. The system will create a history by played track.</li></ul>", "radio-ht" )
				],
				[
					"type"  => "url",
					"label" => __( "Metadata endpoint", "radio-ht" ),
					"id"    => "rht_stream_metadata_endpoint"
				],
				[
					"type"  => "text",
					"label" => __( "Username (optional)", "radio-ht" ),
					"id"    => "rht_stream_username"
				],
				[
					"type"  => "password",
					"label" => __( "Password (optional)", "radio-ht" ),
					"id"    => "rht_stream_password"
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
		if ( ! isset( $_POST['rht_stream_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['rht_stream_meta_box_nonce'], 'rht_stream_save_meta_box_data' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( isset( $_POST['post_type'] ) && 'rht_radio_stream' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		}

		foreach ( $this->config['fields'] as $field ) {
			if ( ! isset( $_POST[ $field['id'] ] ) ) {
				continue;
			}
			$value = $_POST[ $field['id'] ];

			switch ( $field['type'] ) {
				case 'text':
					$sanitized_value = sanitize_text_field( $value );
					break;
				case 'url':
					$sanitized_value = esc_url_raw( $value );
					break;
				case 'password':
					$sanitized_value = sanitize_text_field( $value );
					break;
				default:
					$sanitized_value = sanitize_text_field( $value );
			}

			update_post_meta( $post_id, $field['id'], $sanitized_value );
		}
	}

	public function add_meta_box_callback( $post ) {
		wp_nonce_field( 'rht_stream_save_meta_box_data', 'rht_stream_meta_box_nonce' );
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
            <tr>
                <th scope="row"><?php _e( "Test results", "radio-ht" ); ?></th>
                <td>
                    <input type="button" data-micromodal-trigger="rht-test-modal"
                           value="<?php _e( "Test results", "radio-ht" ); ?>"
                           class="button button-primary"/>
                    <div class="micromodal" id="rht-test-modal" aria-hidden="true">
                        <div class="micromodal__overlay" tabindex="-1" data-micromodal-close>
                            <div class="micromodal__container" role="dialog" aria-modal="true"
                                 aria-labelledby="rht-test-modal-title">
                                <h1 id="rht-test-modal-title"><?php _e( "Test results", "radio-ht" ); ?></h1>
                                <div id="result-content" style="margin-top:20px; margin-bottom:20px;"></div>
                                <div>
                                    <button aria-label="<?php _e( "Close modal", "radio-ht" ); ?>" class="button"
                                            data-micromodal-close><?php _e( "Close", "radio-ht" ); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
		<?php
	}

	private function field( $field, $post ) {
		$value = get_post_meta( $post->ID, $field['id'], true );
		?>
		<?php switch ( $field['type'] ):
			case 'select': ?>
                <select id="<?= esc_attr( $field['id'] ); ?>" name="<?= esc_attr( $field['id'] ); ?>">
					<?php foreach ( $field['options'] as $key => $label ): ?>
                        <option value="<?= esc_attr( $key ); ?>" <?= selected( $value, $key ); ?>><?= esc_html( $label ); ?></option>
					<?php endforeach; ?>
                </select>
				<?php if ( isset( $field['help'] ) ): ?>
                    <div class="rht-help"><?= wp_kses_post( $field['help'] ); ?></div>
				<?php endif; ?>
				<?php break;
			case 'url': ?>
                <input type="url" id="<?= esc_attr( $field['id'] ); ?>" name="<?= esc_attr( $field['id'] ); ?>"
                       value="<?= esc_url( $value ); ?>" class="regular-text"/>
				<?php break;
			case 'text':
				echo '<input type="text" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
				break;
			case 'password':
				echo '<input type="password" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
				break;
		endswitch; ?>
		<?php
	}
}