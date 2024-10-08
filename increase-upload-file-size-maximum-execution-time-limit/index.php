<?php
/*
 * Plugin Name:		Increase maximum upload file size & execution time
 * Description:		To increase WP limits
 * Text Domain:		increase-upload-file-size-maximum-execution-time-limit
 * Domain Path:		/languages
 * Version:			2.0
 * WordPress URI:	https://wordpress.org/plugins/increase-upload-file-size-maximum-execution-time-limit/
 * Plugin URI:		https://puvox.software/software/wordpress-plugins/?plugin=increase-upload-file-size-maximum-execution-time-limit
 * Contributors: 	puvoxsoftware,ttodua
 * Author:			Puvox.software
 * Author URI:		https://puvox.software/
 * Donate Link:		https://paypal.me/Puvox
 * License:			GPL-3.0
 * License URI:		https://www.gnu.org/licenses/gpl-3.0.html
 
 * @copyright:		Puvox.software
*/

 



namespace IncreaseMaximumUploadExecutionLimits
{
  if (!defined('ABSPATH')) exit;
  require_once( __DIR__."/library_default_puvox.php" );


  class PluginClass extends \Puvox\default_plugin
  {

	public function declare_settings()
	{
		$this->initial_static_options	=
		[
			'has_pro_version'		=>0, 
			'show_opts'				=>true, 
			'show_rating_message'	=>true, 
			'show_donation_popup'	=>true, 
			'display_tabs'			=>[],
			'required_role'			=>'install_plugins',
			'default_managed'		=>'network',			// network | singlesite
		];

		$this->initial_user_options		= 
		[
			'upload_size'	 => 10,
			'execution_time' => 30,
			'force_ini_set'	 => true,
			'force_htaccess' => false
		];
	}

	public function __construct_my()
	{
		add_filter('upload_size_limit',	function() {  return $this->opts['upload_size']*1024*1024; }, 11);	
		set_time_limit($this->opts['execution_time']);

		// INI approach
		$this->ini_exists = (function_exists('ini_get') && function_exists('ini_set'));
		if ($this->opts['force_ini_set']){
			@ini_set('upload_max_filesize',	$this->opts['upload_size'].'M'); 
			@ini_set('post_max_size',		$this->opts['upload_size'].'M');   //@ini_set('upload_max_size',	$this->opts['upload_size'].'M'); 	//wp-specific
			@ini_set('max_input_time', 		$this->opts['execution_time']);
			@ini_set('max_execution_time', 	$this->opts['execution_time']);
		}
		
		//@ini_set('memory_limit', '256M'); 
	}
	// ============================================================================================================== //



	// =================================== Options page ================================ //
	public function opts_page_output()
	{ 
		$this->settings_page_part("start");
		?> 

		<style>
		p.submit { text-align:center; }
		.settingsTitle{display:none;}
		.myplugin {padding:10px;}
		</style>
		
		<?php if ($this->active_tab=="Options") 
		{
			//if form updated
			if( $this->checkSubmission() ) 
			{
				$this->opts['upload_size']		= (int)$_POST[ $this->plugin_slug ]['upload_size'];
				$this->opts['execution_time']	= (int)$_POST[ $this->plugin_slug ]['execution_time'];
				$this->opts['force_ini_set'] 	= !empty($_POST[ $this->plugin_slug ]['force_ini_set']);
				$this->opts['force_htaccess'] 	= !empty($_POST[ $this->plugin_slug ]['force_htaccess']);
				if ($this->opts['force_htaccess']){
					$code = 
						"php_value upload_max_filesize ".$this->opts['upload_size']."M".  "\r\n".	
						"php_value post_max_size "		.$this->opts['upload_size']."M".  "\r\n".	
						"php_value max_input_time "		.$this->opts['execution_time'].	  "\r\n".	
						"php_value max_execution_time "	.$this->opts['execution_time']
						;
					$this->helpers->add_into_htaccess($code);
				}
				else{
					$this->helpers->add_into_htaccess('');
				}
				$this->update_opts(); 
			}
			?> 

		  <form class="mainForm" method="post" action="">

			<table class="form-table">
				<tbody>
				<tr class="def">
					<td>
						<label for="upload_size">
							<?php _e('Maximum upload file size (MB)');?>
						</label>
					</td>
					<td>
						<input id="upload_size" name="<?php echo $this->plugin_slug;?>[upload_size]" type="number" value="<?php echo $this->opts['upload_size'];?>"   />
					</td>
				</tr>
				<tr class="def">
					<td>
						<label for="execution_time">
							<?php _e('Maximum execution time (seconds). Server default is typically 30 seconds, so try not to abuse the server with too high value.');?>
						</label>
					</td>
					<td>
						<input id="execution_time" name="<?php echo $this->plugin_slug;?>[execution_time]" type="number" value="<?php echo $this->opts['execution_time'];?>" min="1" max="120" />
					</td>
				</tr>
				<tr class="def">
					<td>
						<label for="force_init">
							<?php _e('Force settings by using <code>ini_set</code> approach, in addition to native hook (if you see some errors related to this functionality, disable this checkbox)');?>
						</label>
					</td>
					<td>
						<input id="force_init" name="<?php echo $this->plugin_slug;?>[force_ini_set]" type="checkbox" value="1" <?php checked($this->opts['force_ini_set']); ?>  />
					</td>
				</tr>
				<tr class="def">
					<td>
						<label for="force_htaccess">
							<?php _e('Force settings by using commands <code>.htaccess</code> file. (if other settings does not work, you can try this. However, this only works in APACHE server types. <b>NOTE, THIS REWRITES HTACCESS FILE, SO BACKUP IT AT FIRST IF UNSURE</b>)');?>
						</label>
					</td>
					<td>
						<input id="force_htaccess" name="<?php echo $this->plugin_slug;?>[force_htaccess]" type="checkbox" value="1" <?php checked($this->opts['force_htaccess']); ?>  />
					</td>
				</tr>
				</tbody>
			</table>

			<?php $this->nonceSubmit(); ?>

		  </form>

		<?php 
		} 
		
		
		$this->settings_page_part("end");
	} 
  } // End Of Class

  $GLOBALS[__NAMESPACE__] = new PluginClass();

} // End Of NameSpace
#endregion

  