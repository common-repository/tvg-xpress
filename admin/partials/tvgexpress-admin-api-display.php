<div class="wrap"><div id="icon-tools" class="icon32"></div>
<?php
if ( isset( $_GET['success'] ) && ! empty( $_GET['success'] ) ) {
	?>
			<div class="notice notice-success is-dismissible">
				<?php echo 'API Settings where updated!'; ?>
	  </div>
	<?php
}
?>
	  <h2><?php echo esc_attr( $this->settings['title'] ); ?></h2>

	  <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" class="api-key-form">
	  <input type="hidden" name="action" value="<?php echo esc_html( $this->settings['action'] ); ?>">
		  <?php wp_nonce_field( 'api_form_action', 'api_form_nonce' ); ?>
	  <table class="form-table" role="presentation">
		  <tbody>
			<?php foreach ( $this->settings['fields'] as $field ) : ?>
			<tr>
			  <th><?php echo esc_html( $field['label'] ); ?></th>
			  <td>
				<?php if ( empty( $field['type'] ) || 'text' === $field['type'] ) : ?>
				  <input type="text" name="<?php echo esc_html( $field['name'] ); ?>" class="<?php echo esc_html( $field['name'] ); ?>" value="<?php echo esc_html( $field['value'] ); ?>" placeholder="<?php echo esc_html( $field['label'] ); ?>">
				<?php elseif ( empty( $field['type'] ) || 'password' === $field['type'] ) : ?>
				  <input type="password" name="<?php echo esc_html( $field['name'] ); ?>" class="<?php echo esc_html( $field['name'] ); ?>" value="<?php echo esc_html( $field['value'] ); ?>" placeholder="<?php echo esc_html( $field['label'] ); ?>">
				<?php elseif ( 'checkbox' === $field['type'] ) : ?>
				  <input type="hidden" name="<?php echo esc_html( $field['name'] ); ?>" value="0">
				  <input type="checkbox" name="<?php echo esc_html( $field['name'] ); ?>" class="<?php echo esc_html( $field['name'] ); ?>"  
														  <?php
															if ( ! empty( $field['value'] ) ) :
																?>
						checked="<?php echo esc_html( $field['value'] ); ?>" <?php endif; ?> value="1">
				<?php endif; ?>
			  </td>
			</tr>
			<?php endforeach; ?>
		  </tbody>
		</table>
		<input type="submit" name="submit" id="submit" class="update-button button button-primary" value="<?php echo esc_html( __( 'Save Changes' ) ); ?>"  />
	</form>

	<br/>
	<h2>Hreinsa skyndiminni</h2>
	<p>Verð og upplýsingar um EIMSKIP sendingar eru geymdar í skyndiminni. Smelltu á <strong>Hreinsa</strong> til að hreinsa skyndiminnið.</p>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" class="api-key-form">
		<input type="hidden" name="action" value="tvg_delete_transient">
		<input type="submit" name="submit" id="submit" class="update-button button button-primary" value="<?php echo esc_html( __( 'Hreinsa' ) ); ?>"  />
	</form>
</div>
