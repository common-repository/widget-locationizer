<?php if( $this->checkLocationizerOn() ) {?>
<p><strong>Locationizer Settings</strong></p>
<p>
<label for="<?php echo $id_disp; ?>-widget-locationizer-tags">Widget Locationizer Tags: 
	<input class="widefat" name="<?php echo $id_disp; ?>-widget-locationizer-tags" id="<?php echo $id_disp; ?>-widget-locationizer-tags" value="<?php echo attribute_escape( $widgetOptions[ 'tags' ] ); ?>" type="text"> </label>
	<br>A comma delimited list of tag pages on which you wish this widget to appear.
</p>
<p>
<label for="<?php echo $id_disp; ?>-widget-locationizer-categories">Widget Locationizer Categories: 
	<input class="widefat" name="<?php echo $id_disp; ?>-widget-locationizer-categories" id="<?php echo $id_disp; ?>-widget-locationizer-categories" value="<?php echo attribute_escape( $widgetOptions[ 'categories' ] ); ?>" type="text"> </label>
	<br>A comma delimited list of category pages on which you wish this widget to appear.
</p>
<p>
<label for="<?php echo $id_disp; ?>-widget-locationizer-posts">Post &amp; Page IDs: 
	<input class="widefat" name="<?php echo $id_disp; ?>-widget-locationizer-posts" id="<?php echo $id_disp; ?>-widget-locationizer-posts" value="<?php echo attribute_escape( $widgetOptions[ 'posts' ] ); ?>" type="text"> </label>
	<br>A comma delimited list of post and pages IDs on which you wish this widget to appear.
</p>
<p>
<label for="<?php echo $id_disp; ?>-widget-locationizer-no-posts">Post &amp; Page IDs to not Display Widget: 
	<input class="widefat" name="<?php echo $id_disp; ?>-widget-locationizer-no-posts" id="<?php echo $id_disp; ?>-widget-locationizer-no-posts" value="<?php echo attribute_escape( $widgetOptions[ 'no-posts' ] ); ?>" type="text"> </label>
	<br>A comma delimited list of post and pages IDs on which you do not wish this widget to appear.
</p>
<p>
	<label for="<?php echo $id_disp; ?>-widget-locationizer-other-pages">Should this widget appear on all non-tag and non-category pages?<br />
	<select id="<?php echo $id_disp; ?>-widget-locationizer-other-pages" name="<?php echo $id_disp; ?>-widget-locationizer-other-pages">
		<option <?php selected( 0, $widgetOptions[ 'other-pages' ] ); ?> value="0">No</option>
		<option <?php selected( 1, $widgetOptions[ 'other-pages' ] ); ?> value="1">Yes</option>
	</select> </label>
</p>
<?php } ?>
<?php if( $this->checkNofollowOn() ) {?>
<p><strong>Follow Settings</strong></p>
<p>
	<label for="<?php echo $id_disp; ?>-widget-locationizer-follow-type">Should this widget use DoFollow or NoFollow?<br />
	<select id="<?php echo $id_disp; ?>-widget-locationizer-follow-type" name="<?php echo $id_disp; ?>-widget-locationizer-follow-type">
		<option <?php selected( '', $widgetOptions[ 'follow-type' ] ); ?> value="">Leave As Is</option>
		<option <?php selected( 'dofollow', $widgetOptions[ 'follow-type' ] ); ?> value="dofollow">DoFollow</option>
		<option <?php selected( 'nofollow', $widgetOptions[ 'follow-type' ] ); ?> value="nofollow">NoFollow</option>
	</select> </label>
</p>
<?php } ?>