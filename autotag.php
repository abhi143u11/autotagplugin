<?php
/**
 * Plugin Name: Add Tags to Post Title
 *  Description: Making Post title unique
 */

function add_tags_to_post_title($post_ID){
  
    $disable_on_post = esc_attr(get_option('disable_on_post'));
    $org_title = get_the_title($post_ID);
    add_post_meta( $post_ID, 'bpx_org_title',  $org_title  ); 

    $post_title = get_the_title($post_ID);
   
     
    $prefix_text = esc_attr(get_option('prefix_text'));
    $suffix_text = esc_attr(get_option('suffix_text'));

    $words_to_remove = explode(',', esc_attr(get_option('words_to_remove'))); 

  $tags = "";
  if ($words_to_remove) {
  

  foreach($words_to_remove as $findString) {

    $post_title =  preg_replace('/\b('.preg_quote($findString).')\b/i', "", $post_title);


  }
}

    $result = str_replace($words_to_remove, "", $post_title );
   
  
    $posttags = get_the_tags($post_ID);
if ($posttags) {
  
    $i= 1;
  foreach($posttags as $tag) {
    if($i<5){
       
    $tags .= $tag->name . ', '; 
     
    }
    $i++;
  }
}

$final_post_title = trim($prefix_text." ".$post_title);


$final_post_title = preg_replace('/\s+/', ' ', $final_post_title);

$final_post_title = implode(',',array_unique(explode(',', $final_post_title)));

$final_post_title = rtrim($final_post_title, ',');
$final_post_title = rtrim($final_post_title, '-');


if($disable_on_post != "on"){
$final_post_title = $final_post_title." ".$tags;
}
$final_post_title = rtrim(trim($final_post_title),",");
$final_post_title = $final_post_title." ".$suffix_text;
$final_post_title = implode(' ', array_unique(explode(' ', $final_post_title)));


  if ( ! wp_is_post_revision( $post_id ) ){
     
        // unhook this function so it doesn't loop infinitely
        remove_action('save_post', 'add_tags_to_post_title');
     
        // update the post, which calls save_post again
        wp_update_post( [
            'ID'         => $post_ID,
            'post_title' => $final_post_title,
        ] );
 
        // re-hook this function
        add_action('save_post', 'add_tags_to_post_title');
    }

}
// add_filter( 'default_title', 'add_tags_to_post_title', 10, 2 );
add_action('save_post', 'add_tags_to_post_title');
//add_action( 'pre_post_update', 'add_tags_to_post_title',  10, 2 ); 


// create custom plugin settings menu
add_action('admin_menu', 'bpx__plugin_create_menu');

function bpx__plugin_create_menu() {

	//create new top-level menu
	add_menu_page('Add Tags to Post Title Settings', 'Auto Tags Settings', 'administrator', __FILE__, 'bp_autotag_plugin_settings_page' , 'dashicons-admin-page' );

	//call register settings function
	add_action( 'admin_init', 'register_bp_autotag_plugin_settings' );
}


function register_bp_autotag_plugin_settings() {
	//register our settings
	register_setting( 'bp-autotag-plugin-settings-group', 'prefix_text' );
	register_setting( 'bp-autotag-plugin-settings-group', 'suffix_text' );
	register_setting( 'bp-autotag-plugin-settings-group', 'words_to_remove' );
  register_setting( 'bp-autotag-plugin-settings-group', 'disable_on_post' );
}

function bp_autotag_plugin_settings_page() {
?>
<div class="wrap">
<h1>Add Tags to Post Title</h1>

<form method="post" action="options.php">
    <?php settings_fields( 'bp-autotag-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'bp-autotag-plugin-settings-group' ); ?>
    <table class="form-table" width="800">
        <tr valign="top">
        <th scope="row">Prefix Text</th>
        <td><input type="text" name="prefix_text" value="<?php echo esc_attr( get_option('prefix_text') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Suffix Text</th>
        <td><input type="text" name="suffix_text" value="<?php echo esc_attr( get_option('suffix_text') ); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Words to remove</th>
        <td><textarea  name="words_to_remove"><?php echo esc_attr(get_option('words_to_remove')); ?></textarea></td>
        </tr>
        <tr valign="top">
        <th scope="row">Disable on Post Title</th>
        <td><input type="checkbox" name="disable_on_post" <?php if ( "on" == esc_attr(get_option('disable_on_post'))) echo 'checked="checked"'; ?> ></td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php } ?>