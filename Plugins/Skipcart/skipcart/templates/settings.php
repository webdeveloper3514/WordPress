<?php
include CURR_PLUGIN_URL.'skipcart-config.php';


if( isset( $_POST['save_btn'] ) ){    
   $handsome_checkout_url = esc_attr( $_POST["handsome_checkout_url"] );   
   $advertisement_layout = $_POST["advertisement_layout"];
   
   
   update_option( "handsome_checkout_url",$handsome_checkout_url );   
   update_option( "advertisement_layout",$advertisement_layout );
   
    // Build your file contents as a string
    $file_contents = '<?xml version="1.0" ?><yourroot><item>'.stripslashes( $_POST["advertisement_layout"] ).'</item></yourroot></xml>';
    
    // Open or create a file (this does it in the same dir as the script)
    $my_file = fopen("../advertisement.xml", "w");
    fwrite($my_file, $file_contents);
    fclose($my_file);
}


?>
<div class="wrap">
<h1>Skip Cart Settings</h1>
<form method="POST" action="">     

    <div class="form-group">
        <label for="url">Handsome Checkout Page URL:</label>
        <input type="text" value="<?php echo get_option( 'handsome_checkout_url','' ); ?>" name="handsome_checkout_url" class="form-control" id="handsome_checkout_url">
    </div>

    <div class="form-group">
        <label for="advertisement"> Advertisement Layout:</label>
       <?php
        $value = stripslashes( get_option( 'advertisement_layout','' ) ); ;
                        $id = 'advertisement_layout';
                        $settings = array(
                            'quicktags' => true,
                            'tinymce' => true,
                            'textarea_rows' => 20,
                        );
                        wp_editor($value, $id, $settings);
                        ?>
    </div>

    <p>
        <input type="submit" name="save_btn" value="Save settings" class="button-primary"/>
    </p>
</form>
</div>


