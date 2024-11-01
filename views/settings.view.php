<div class="wrap">
	<form method="post" action="">
		<h2>Locationizer Settings</h2>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="use-locationizer-settings">Use Locationizer Settings?</label></th>
					<td><input <?php checked( 1, $this->options[ 'use-locationizer-settings' ] ); ?> type="checkbox" value="1" id="use-locationizer-settings" name="use-locationizer-settings" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="use-nofollow-settings">Use Follow Settings?</label></th>
					<td><input <?php checked( 1, $this->options[ 'use-nofollow-settings' ] ); ?> type="checkbox" value="1" id="use-nofollow-settings" name="use-nofollow-settings" /></td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" value="<?php _e( 'Save Settings' ); ?>" id="submit" />
		</p>
	</form>
</div>