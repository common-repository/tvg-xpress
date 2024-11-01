<div class="wrap">
  <h1>EIMSKIP</h1>
	<?php
	// Let see if we have a caching notice to show.
	$admin_notice = get_option( 'tvg_admin_notice' );
	if ( $admin_notice ) {
		// We have the notice from the DB, lets remove it.
		delete_option( 'tvg_admin_notice' );
		// Call the notice message.
		$this->admin_notice( $admin_notice );
	}
	if ( isset( $_GET['settings-updated'] ) ) {
		$settings_updated = sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) );
		if ( $settings_updated ) {
			$this->admin_notice( 'Your settings have been updated!' );
		}
	}
	?>
	<form method="POST" action="options.php">
	  <?php
		settings_fields( 'tvg-options' );
		do_settings_sections( 'tvg-options' );
		submit_button();
		?>
	</form>
</div>
