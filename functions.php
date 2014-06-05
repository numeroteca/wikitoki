<?php 
// extra fields in user profile
add_action( 'show_user_profile', 'extra_user_profile_fields' );
add_action( 'edit_user_profile', 'extra_user_profile_fields' );

	function extra_user_profile_fields( $user ) {
 		$extra_fields = array(
			array(
				'name' => 'Web (URL) <br>Ej: http://wikitoki.org',
				'label' => 'web'
			),
			array(
				'name' => 'Feed (rss) <br>Ej: http://wikitoki.org/feed',
				'label' => 'feed'
			),
			array(
				'name' => 'Twitter <br>Ej: wiki_toki',
				'label' => 'twitter'
			),
			array(
				'name' => 'Facebook <br>Ej http://facebook.com/wikitoki',
				'label' => 'facebook'
			),
		);
		$extra_textareas = array(
			array(
				'name' => 'Descripción',
				'label' => 'description',
			),
		);
	?>

		<h3><?php _e("Información", "blank"); ?></h3>
		<table class="form-table">
	<?php foreach ( $extra_fields as $extra_field ) { ?>	
		<tr>
		<th><label for="<?php echo $extra_field['label']; ?>"><?php echo $extra_field['name']; ?></label></th>
		<td>
			<input type="text" name="<?php echo $extra_field['label']; ?>" id="<?php echo $extra_field['label']; ?>" value="<?php echo esc_attr( get_the_author_meta( $extra_field['label'], $user->ID ) ); ?>" class="regular-text" /><br />
		</td>
		</tr>

	<?php } ?>
	
	<?php foreach ( $extra_textareas as $extra_field ) { ?>	
		<tr>
		<th><label for="<?php echo $extra_field['label']; ?>"><?php echo $extra_field['name']; ?></label></th>
		<td>
			<textarea name="<?php echo $extra_field['label']; ?>" id="<?php echo $extra_field['label']; ?>" rows="5" cols="30"><?php echo esc_attr( get_the_author_meta( $extra_field['label'], $user->ID ) ); ?></textarea><br />
		</td>
		</tr>

	<?php } ?>

		</table>

<?php }

add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );
 
function save_extra_user_profile_fields( $user_id ) {
 
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
 		$extra_fields = array(
			array(
				'name' => 'Web (URL) <br>Ej: http://wikitoki.org',
				'label' => 'web'
			),
			array(
				'name' => 'Feed (rss) <br>Ej: http://wikitoki.org/feed',
				'label' => 'feed'
			),
			array(
				'name' => 'Twitter <br>Ej: wiki_toki',
				'label' => 'twitter'
			),
			array(
				'name' => 'Facebook <br>Ej http://facebook.com/wikitoki',
				'label' => 'facebook'
			),
		);

	foreach ( $extra_fields as $extra_field ) {	
		update_user_meta( $user_id, $extra_field['label'], $_POST[$extra_field['label']] );
	}

}

////////////////// USER TAXONOMIES
// Custom User Taxonomies
add_action( 'init', 'build_user_taxonomies', 0 );

function build_user_taxonomies() {
	// 
	register_taxonomy( 'user-type', 'user', array(
		'labels' => array(
			'name' => _x( 'Tipos de usuario','taxonomy general name' ),
			'singular_name' => _x( 'Tipo de usuario','taxonomy general name' ),		
			'search_items' => __( 'Busca entre los tipos de usuario' ),
			'popular_items' => __( 'Tipos de usuario populares' ),
			'all_items' => __( 'Todos los tipos de usuario' ),
			'parent_item' => __( 'Tipo de usuario padre' ),
			'edit_item' => __( 'Modificar tipo de usuario' ),
			'update_item' => __( 'Actualizar' ),
			'add_new_item' => __( 'Añadir nuevo tipo de usuario' ),
			'new_item_name' => __( 'nuevo tipo de usuario' ),
//			'separate_items_with_commas' => __( 'Separate tags with commas' ),
//			'add_or_remove_items' => __( 'Add or remove tags' ),
//			'choose_from_most_used' => __( 'Choose from the most used tags' ),
//			'menu_name' => 
		),
		'public' => true,
		'hierarchical' => true,
	//	'update_count_callback' => 'my_update_professionnel_count', // Use a custom function to update the count. TODO
//		'query_var' => true,
		'rewrite' => array('slug'=>'pro','with_front'=>false,'hierarchical'=>true),
		'capabilities' => array(
			'manage_terms' => 'edit_users', // Using 'edit_users' cap to keep this simple.
			'edit_terms'   => 'edit_users',
			'delete_terms' => 'edit_users',
			'assign_terms' => 'read',
		),
	));
}

/* Adds user taxonomies page in the admin. */
add_action( 'admin_menu', 'my_add_user_type_admin_page' );


/**
 * Creates the admin pages for the 'professionnel', 'secteur' and 'lieu' taxonomies under the 'Users' menu.  It works the same as any 
 * other taxonomy page in the admin.  However, this is kind of hacky and is meant as a quick solution.  When 
 * clicking on the menu item in the admin, WordPress' menu system thinks you're viewing something under 'Posts' 
 * instead of 'Users'.  We really need WP core support for this.
 */
function my_add_user_type_admin_page() {

	$tax = get_taxonomy( 'user-type' );

	add_users_page(
		esc_attr( $tax->labels->menu_name ),
		esc_attr( $tax->labels->menu_name ),
		$tax->cap->manage_terms,
		'edit-tags.php?taxonomy=' . $tax->name
	);
}

/* Open the right admin menu when clicking in user taxonomies: Professionnel, Secteur, Lieu */
add_filter( 'parent_file', 'fix_user_tax_page' );

function fix_user_tax_page( $parent_file = '' ) {
	global $pagenow;

	if ( ! empty( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] == 'user-type' && $pagenow == 'edit-tags.php' ) {
		$parent_file = 'users.php';
	}

	return $parent_file;
}

/* Add section to the edit user page in the admin to select profession, secteur or lieu */
add_action( 'show_user_profile', 'my_edit_user_type_section' );
add_action( 'edit_user_profile', 'my_edit_user_type_section' );

/**
 * Adds an additional settings section on the edit user/profile page in the admin.  This section allows users to 
 * select a profession, secteur or lieu from a checkbox of terms from each taxonomy.
 *
 * @param object $user The user object currently being edited.
 */

// professionnel
function my_edit_user_type_section( $user ) {

	$tax = get_taxonomy( 'user-type' );

	/* Make sure the user can assign terms of the profession taxonomy before proceeding. */
	if ( !current_user_can( $tax->cap->assign_terms ) )
		return;

	/* Get the terms of the 'profession' taxonomy. */
	$terms = get_terms( 'user-type', array( 'hide_empty' => false ) ); ?>

	<h3><?php _e( 'Tipo de usuario' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="user-type"><?php _e( 'Elige uno de los tipos' ); ?></label></th>
			<td>
				<fieldset id="user-type">
			<?php
			/* If there are any profession terms, loop through them and display checkboxes. */
			if ( !empty( $terms ) ) {
				foreach ( $terms as $term ) { ?>
					<input type="checkbox" name="user-type-<?php echo esc_attr( $term->slug ); ?>" id="user-type-<?php echo esc_attr( $term->slug ); ?>" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( true, is_object_in_term( $user->ID, 'user-type', $term->slug ) ); ?> /> <label for="user-type-<?php echo esc_attr( $term->slug ); ?>"><?php echo $term->name; ?></label> <br />
				<?php }
			}
			/* If there are no profession terms, display a message. */
			else {
				_e( 'No hay usuarios de este tipo.' );
			}
			?></fieldset></td>
		</tr>
	</table>
<?php }

/* Update the profession terms when the edit user page is updated. */
add_action( 'personal_options_update', 'my_save_user_type_terms' );
add_action( 'edit_user_profile_update', 'my_save_user_type_terms' );

/**
 * Saves the term selected on the edit user/profile page in the admin. This function is triggered when the page 
 * is updated.  We just grab the posted data and use wp_set_object_terms() to save it.
 *
 * @param int $user_id The ID of the user to save the terms for.
 */

// professionnel
function my_save_user_type_terms( $user_id ) {

	$tax = get_taxonomy( 'user-type' );

	/* Make sure the current user can edit the user and assign terms before proceeding. */
	if ( !current_user_can( 'edit_user', $user_id ) && current_user_can( $tax->cap->assign_terms ) )
		return false;

	$terms = get_terms('user-type','hide_empty=0');
	$add_terms = array();
	foreach ( $terms as $term ) {
		$toadd = esc_attr( $_POST['user-type-' .$term->slug] );
		if ( $toadd != '' ) { array_push($add_terms, $term->slug); }
	}

	/* Sets the terms (we're just using a single term) for the user. */
	wp_set_object_terms( $user_id, $add_terms, 'user-type', false);

	clean_object_term_cache( $user_id, 'user-type' );
}