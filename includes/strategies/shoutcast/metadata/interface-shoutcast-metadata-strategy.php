<?php

interface Shoutcast_Metadata_Strategy_Interface {
	public function get_track_history( $url, $username = null, $password = null );
}