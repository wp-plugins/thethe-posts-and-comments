<?php /** @version $Id: view-settings.php */ ?>
<?php $config = $this->config();?>
<form method="post" action="">
<?php include 'inc.submit-buttons.php';?> 
<fieldset>
  <legend><?php _e('Cusom CSS','thethe-posts-n-comments-widget'); ?></legend>
  <ul class="thethe-settings-list">
   <li>
      <label for="data-enable-custom-css"><?php _e('Enable Custom CSS','thethe-posts-n-comments-widget'); ?>:</label>
      <input name="data[enable-custom-css]" id="data-enable-custom-css" class="str-field"  type="checkbox" <?php if ($config['enable-custom-css']) echo 'checked="checked"'; ?> >
      <a class="tooltip" href="javascript:void(0);">?<span><?php _e('Check this checkbox to enable the custom css on your site.','thethe-posts-n-comments-widget'); ?></span></a> 
	</li> 
    <li>
      <label for="data-custom-css"><?php _e('Custom CSS','thethe-posts-n-comments-widget'); ?>:</label>
		<textarea rows="10" cols="50" name="data[custom-css]" id="data-custom-css"><?php
			echo $config['custom-css'];
		?></textarea>
	</li>
  </ul>
</fieldset>

<?php include 'inc.submit-buttons.php';?> 
</form>