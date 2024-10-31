<?php
    /**
     * Plugin Name: Multisite Enhancer
     * Description: Adds extra features to multisite network dashboard such as posts count.
     * Version: 0.3.2
     * Author: Krishna
     * Author URI: https://shrikrishnameena.com
     * Requires at least: 3.5
     *
     * Text Domain: ms-enhancer
     * Domain Path: /languages
     
     * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
     */
    
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
    }
    
    function msehcr_activate() {
        $allnet_sites = array();
        add_site_option("MSEHCR_ALLNET_URIS", $allnet_sites);
        add_site_option("MSEHCR_FeedWordpressExtLnkSel", ".syndicated-attribution a");
        add_site_option("MSEHCR_HeaderScripts", "");
    }
    register_activation_hook( __FILE__, 'msehcr_activate' );
    
    function msehcr_add_admin_menu() {
        add_menu_page( 'MS Enhancer: Settings', 'MS Enhancer', 'manage_options', 'msehcr_settings', 'msehcr_settings_page');
    }
    function msehcr_settings_page(){
        if(isset($_POST['msehcr_update_settings'])){
            $allnet_sites = str_replace(array(" ", "\r", "\n"), "", $_POST['msehcr_allnet_sites']);
            if(!empty($allnet_sites)){
                $allnet_sites=explode(",", $allnet_sites);
                $update_status=update_site_option("MSEHCR_ALLNET_URIS", $allnet_sites);
            }
            $msehcr_FWELS = $_POST['msehcr_FWELS'];
            if(!empty($msehcr_FWELS)){
                $update_status_FWELS=update_site_option("MSEHCR_FeedWordpressExtLnkSel", $msehcr_FWELS);
            }
            $msehcr_headerscripts = $_POST['msehcr_headerscripts'];
            if(!empty($msehcr_headerscripts)){
                $update_status_headerscripts=update_site_option("MSEHCR_HeaderScripts", $msehcr_headerscripts);
            }
        }
        ?>
        <div class="wrap">
            <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
            <p><a target="_new" href="<?=get_site_url()?>?mse_posts_count">Click here</a> to view this network posts count.</p>
            <p style="color:blue;">
                <?php echo isset($update_status) ? ($update_status ? "Network Site URIs Updated!" : "Network Site URIs Not Updated!") . "<br>" : ""; ?>
                <?php echo isset($update_status_FWELS) ? ($update_status_FWELS ? "External Links Selector Updated!" : "External Links Selector Not Updated!") . "<br>" : ""; ?>
                <?php echo isset($update_status_headerscripts) ? ($update_status_headerscripts ? "Header Script Updated!" : "Header Scripts Not Updated!") . "<br>" : ""; ?>
            </p>
            <form method="post">
                <fieldset style="background-color:lightblue;padding:10px;">
                    <label for="msehcr_FWELS">
                        <h3>External Links jQuery/CSS Selector (To open External Links in child window) (Comma Sperated)</h3>
                        <input class="regular-text" id="msehcr_FWELS" name="msehcr_FWELS" value="<?=get_site_option("MSEHCR_FeedWordpressExtLnkSel");?>" />
                    </label>
                </fieldset>
                <fieldset style="background-color:lightgreen;padding:10px;">
                    <h3>Your other Wordpress network URIs (Comma Seperated), where "Multisite Enhancer" plugin is installed. </h3>
                    <p><a target="_new" href="<?=get_site_url()?>?mse_allnet_posts_count">Click here</a> to view all wp networks posts count.</p>
                    <label for="msehcr_allnet_sites">
                        <textarea class="large-text" cols="45" rows="30" id="msehcr_allnet_sites" name="msehcr_allnet_sites"><?=implode(",\n", get_site_option("MSEHCR_ALLNET_URIS"));?></textarea>
                    </label>
                </fieldset>
                <fieldset style="background-color:lightgreen;padding:10px;">
                    <h3>Header Scripts</h3>
                    <label for="msehcr_headerscripts">
                    <textarea class="large-text" cols="45" rows="15" id="msehcr_headerscripts" name="msehcr_headerscripts"><?=stripslashes(get_site_option("MSEHCR_HeaderScripts"));?></textarea>
                    </label>
                </fieldset>
                <?php submit_button('Save all changes', 'primary','msehcr_update_settings', TRUE); ?>
            </form>
        </div>
        <?php
    }
    add_action("network_admin_menu", "msehcr_add_admin_menu");
    
    if(!function_exists('msehcr_wpmu_list_sites')):
        function msehcr_wpmu_list_sites() {
            ob_start();
            $subsites = get_sites();
            
            if ( ! empty ( $subsites ) ) {
                
                echo '<ul class="subsites">';
                
                foreach( $subsites as $subsite ) {
                    
                    $subsite_id = get_object_vars( $subsite )["blog_id"];
                    $subsite_name = get_blog_details( $subsite_id )->blogname;
                    $subsite_link = get_blog_details( $subsite_id )->siteurl;
                    echo '<li class="site-' . $subsite_id . '"><a href="' . $subsite_link . '">' . $subsite_name . '</a></li>';
                    
                }
                
                echo '</ul>';
                return ob_get_clean();
            }
            
        }
        
        add_shortcode("show_sites", "msehcr_wpmu_list_sites");
    endif;
    
    if(!function_exists('msehcr_ms_total_posts')):
        function msehcr_ms_total_posts(){
            $subsites = get_sites();
            $total_posts = 0;
            if ( ! empty ( $subsites ) ) {
                foreach( $subsites as $subsite ) {
                    $subsite_id = get_object_vars( $subsite )["blog_id"];
                    $total_posts += get_blog_details( $subsite_id, 1 )->post_count;
                }
            }
            echo "You have published " . $total_posts . " posts across whole network.";
        }
        add_action("wpmuadminresult", "msehcr_ms_total_posts");
    endif;
    
    
    if(!function_exists('msehcr_wpmu_postcount')):
        function msehcr_wpmu_postcount($args){
            $args["post_count"] = "post_count";
            return $args;
        }
        add_filter("wpmu_blogs_columns", "msehcr_wpmu_postcount");
        add_filter("manage_sites-network_sortable_columns", "msehcr_wpmu_postcount");
    endif;
    
    if(!function_exists('msehcr_wpmu_postcount_column')):
        function msehcr_wpmu_postcount_column($column_name, $blog_id){
            if($column_name == "post_count")
                echo get_blog_details( $blog_id, 1 )->post_count;
            return $blog_id - 1;
        }
        add_action("manage_sites_custom_column", "msehcr_wpmu_postcount_column", 10, 2);
    endif;
    
    if(!function_exists('msehcr_wp_loaded_return_postcount')):
        function msehcr_wp_loaded_return_postcount(){
            if(isset($_REQUEST['mse_posts_count'])):
                $subsites = get_sites();
            $total_posts = 0;
            if ( ! empty ( $subsites ) ) {
                foreach( $subsites as $subsite ) {
                    $subsite_id = get_object_vars( $subsite )["blog_id"];
                    $total_posts += get_blog_details( $subsite_id, 1 )->post_count;
                }
            }
            $output = array("total_posts"=>$total_posts);
            exit(json_encode($output));
            endif;
        }
        add_action('wp_loaded', 'msehcr_wp_loaded_return_postcount');
    endif;
    
    function msehcr_wp_loaded_return_allnet_postcount(){
        if(isset($_REQUEST['mse_allnet_posts_count'])){
            $sites=get_site_option("MSEHCR_ALLNET_URIS");
            $total_posts=0;
            if( ! empty( $sites ) ){
                foreach($sites as $site){
                    $r = file_get_contents($site."?mse_posts_count");
                    $r = json_decode($r);
                    if(isset($r->total_posts)){
                        $total_posts += intval($r->total_posts);
                    }
                }
            }
            $output = array("total_posts"=>$total_posts);
            exit(json_encode($output));
        }
    }
    add_action('wp_loaded', 'msehcr_wp_loaded_return_allnet_postcount');
    
    function msehcr_ElinksPopup(){
        ?>
        <?=stripslashes(get_site_option("MSEHCR_HeaderScripts"));?>
        <script>
            jQuery(document).ready(function(){
                                   jQuery("<?=get_site_option("MSEHCR_FeedWordpressExtLnkSel");?>").click(function(e){
                                                                                                     e.preventDefault();
                                                                                                     x=window.screen.width;
                                                                                                     y=window.screen.height;
                                                                                                     top=(y-600)/2;
                                                                                                     left=(x-800)/2;
                                                                                                     window.open(
                                                                                                                 jQuery(this).attr("href"),
                                                                                                                 "Source",
                                                                                                                 config='height=600,width=800,toolbar=no,menubar=no,scrollbars=no,resizable=yes,location=no,directories=no,status=no,top='+top+',left='+left
                                                                                                                 );
                                                                                                     });
                                   });
            </script>
        <?php
    }
    add_filter("wp_head", "msehcr_ElinksPopup");
    
