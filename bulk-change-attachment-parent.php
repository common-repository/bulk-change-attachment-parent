<?php /*

**************************************************************************

Plugin Name:  Bulk Change Attachment Parent
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/bulk-change-attachment-parent/
Version:      1.0.0
Description:  Allows you to change the parent post/page of an attachment, either in bulk or one at a time. Thanks to <a href="http://lacquerhead.ca/2009/07/change-attachment-parent/">Joel Sholdice</a> for some inspiration.
Author:       Viper007Bond
Author URI:   http://www.viper007bond.com/

**************************************************************************/

class BulkChangeParent {

	// Plugin initialization
	function __construct() {

		// Bulk
		add_action( 'admin_init', array(&$this, 'post_handler') );
		add_action( 'admin_enqueue_scripts', array(&$this, 'maybe_enqueue_javascript') );
		if ( ! empty($_GET['bulkchangedparents']) )
			add_action( 'admin_notices', array(&$this, 'output_success_notice') );

		// Single, props to Joel Sholdice for how to do this
		add_filter( 'attachment_fields_to_edit', array(&$this, 'add_post_parent_edit_field'), 10, 2 );
		add_filter( 'attachment_fields_to_save', array(&$this, 'add_post_parent_save_field'), 10, 2);
	}


	// Registers the Javascript action for the media and custom form pages
	function maybe_enqueue_javascript( $hook_suffix ) {
		if ( 'upload.php' == $hook_suffix || ( !empty($_GET['action']) && 'bulkchangeparent' == $_GET['action'] ) || ( !empty($_GET['action2']) && 'bulkchangeparent' == $_GET['action2'] ) )
			add_action( 'admin_head', array(&$this, 'admin_head') );
	}


	// Outputs the Javascript that adds a new item to the Bulk Actions menu
	function admin_head() {
		$changeparent =  esc_js( __( 'Change Parent', 'bulk-change-attachment-parent' ) );
		
		?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$("select[name=action] > option:last").before("<option value='bulkchangeparent'><?php echo $changeparent; ?></option>");
		$("select[name=action2] > option:last").before("<option value='bulkchangeparent'><?php echo $changeparent; ?></option>");
	});
</script>
<?php
	}


	// Handles form submits, either displaying the form requesting new parent ID or actually doing the parent ID changing
	function post_handler() {
		global $parent_file, $submenu_file;

		// Do the actual reassignment
		if ( !empty( $_POST['bcapnewparent'] ) && ( !empty( $_GET['media'] ) || !empty( $_GET['ids'] ) ) ) {
			if ( !current_user_can('upload_files') )
				wp_die( __( 'You do not have permission to upload files.' ) );

			check_admin_referer('bulkchangeparent');

			$item_ids = isset( $_GET['media'] ) ? $_GET['media'] : explode( ',', $_GET['ids'] );
			$item_ids = array_map( 'intval', $item_ids );

			if ( ! intval( $_POST['bcapnewparent'] )  )
				return;
			$newparent = get_post( $_POST['bcapnewparent'] );
			if ( ! $newparent )
				return;

			$changed = 0;

			foreach ( $item_ids as $item_id ) {
				$item = get_post( $item_id );

				if ( ! $item || $item->ID == $newparent->ID )
					continue;

				$item->post_parent = $newparent->ID;

				$result = wp_update_post( $item );
				if ( $result )
					$changed++;
			}

			$location = 'upload.php';
			if ( $referer = wp_get_referer() ) {
				if ( false !== strpos($referer, 'upload.php') )
					$location = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'message', 'ids', 'posted'), $referer );
			}
			$location = add_query_arg( 'bulkchangedparents', $changed, $location );
			wp_redirect( $location );
			exit();
		}
		
		// Prompt the user for the new parent ID
		elseif (
			( isset($_GET['doaction']) || isset($_GET['doaction2']) )
			&& ( 'bulkchangeparent' == $_GET['action'] || 'bulkchangeparent' == $_GET['action2'] )
			&& ( isset($_GET['media']) || isset($_GET['ids']) )
		) {
			$parent_file = 'upload.php';
			$submenu_file = 'upload.php';

			if ( !current_user_can('upload_files') )
				wp_die( __( 'You do not have permission to upload files.' ) );

			// Failed form submit
			if ( isset( $_POST['bcapnewparent'] ) ) {
				check_admin_referer('bulkchangeparent');
				add_action( 'admin_notices', array(&$this, 'output_fail_notice') );
			} else {
				check_admin_referer('bulk-media');
			}

			$location = 'upload.php';
			if ( $referer = wp_get_referer() ) {
				if ( false !== strpos($referer, 'upload.php') )
					$location = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'message', 'ids', 'posted'), $referer );
			}

			$title = __( 'Bulk Change Parent', 'bulk-change-attachment-parent' );

			require( './admin-header.php' );

?>

<div class="wrap">
<?php screen_icon('upload'); ?>
<h2><?php _e( 'Bulk Change Parent', 'bulk-change-attachment-parent' ); ?></h2>

<form method="post" action="" class="media-upload-form" id="media-single-form">

<p><?php _e( 'Please enter the ID of the new parent item. Make sure to double check your input.', 'bulk-change-attachment-parent' ); ?></p>

<p><?php printf( __( 'If you need help determining the ID of the new parent, install the <a href="%s">Simply Show IDs</a> plugin.', 'bulk-change-attachment-parent' ), 'http://sivel.net/wordpress/simply-show-ids/' ); ?></p>

<p><?php printf( __('New Parent ID: %s', 'bulk-change-attachment-parent' ), '<input type="text" name="bcapnewparent" id="bcapnewparent" size="5" class="regular-text" />' ); ?></p>

<p class="submit">
<input type="submit" class="button-primary" name="bulkchangeparentbutton" value="<?php esc_attr_e( 'Change Parent', 'bulk-change-attachment-parent' ); ?>" />
<?php wp_original_referer_field(true, 'previous'); ?>
<?php wp_nonce_field('bulkchangeparent'); ?>
</p>

</form>

</div>

<?php

			require( './admin-footer.php' );

			exit();
		}
	}


	// Outputs a notice saying how many items were changed
	function output_success_notice() {
		if ( empty($_GET['bulkchangedparents']) || ! $count = (int) $_GET['bulkchangedparents'] )
			return;
		?>
		<div id="message" class="updated"><p><?php echo esc_html( sprintf( _n( 'Changed the parent of %s item.', 'Changed the parent of %s items.', $count, 'bulk-change-attachment-parent' ), $count ) ); ?></p></div>
<?php
	}


	// Outputs an error saying entering a parent ID is required
	function output_fail_notice() { ?>
		<div id="message" class="error"><p><?php esc_html_e( 'Please enter new parent ID.', 'bulk-change-attachment-parent' ); ?></p></div>
<?php
	}


	// Creates a new field on the edit media form
	function add_post_parent_edit_field( $form_fields, $post ) {
		$form_fields['post_parent'] = array(
			'label' => __( 'Parent ID', 'bulk-change-attachment-parent' ),
			'helps' => __( 'The ID of the parent item. Please be careful when changing this.', 'bulk-change-attachment-parent' ),
			'value' => $post->post_parent,
		);

		return $form_fields;
	}


	// Makes the new field on the edit media form get applied
	function add_post_parent_save_field( $post, $attachment ) {
		$post['post_parent'] = (int) $attachment['post_parent'];
		return $post;
	}


	// PHP4 compatibility
	function SyntaxHighlighter() {
		$this->__construct();
	}
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'BulkChangeParent', 5 );
function BulkChangeParent() {
	global $BulkChangeParent;
	$BulkChangeParent = new BulkChangeParent();
}

?>