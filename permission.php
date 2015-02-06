<?php 

namespace Permission;

class Permission {

    private $user_id;


    // Setup class
    public function __construct()
    {
        $this->user_id = get_current_user_id();

        $this->register_role();
        
        if( is_super_admin( $this->user_id ) && is_admin() ) {
            add_action('user_new_form', [&$this, 'add_page_permission_field']);
            add_action('user_register', [&$this, 'save_user_permission']);
            add_action('edit_user_profile_update', [&$this, 'save_user_permission']);
            add_action('edit_user_profile', [&$this, 'add_page_permission_field_to_profile']);
        }

        // Add if not Admins and only in admin view
        if( !is_super_admin( $this->user_id ) && is_admin() ) {
            add_filter('user_has_cap', [&$this, 'user_has_cap'], 10, 4);
            add_action('pre_get_posts', [&$this, 'pre_get_posts'] );
            add_action('views_edit-page', [&$this, 'remove_tabs'] );
            add_filter('page_attributes_dropdown_pages_args', [&$this, 'page_dropdown'], 10, 2 );
        }
    }


    // Change page dropdown to only include users pages
    public function page_dropdown($args, $post)
    {
        $user_page = get_user_meta($this->user_id, 'permission', true);
        $pages     = $this->user_can_edit_pages( $this->user_id );

        $args =  [
            'selected'         => $post->post_parent,
            'name'             => 'parent_id',
            'include'          => $pages,
            'sort_column'      => 'ID',
            'echo'             => false,
        ];

        // If the post id is the users top page
        // Set that parent to selected
        if( $post->ID == $user_page ) {
            $parent = wp_get_post_parent_id($user_page);

            $args['selected'] =  $parent;
            $args['include'] =  array_merge( [$parent], $pages );
        }

        return $args;
    }


    // Remove tabs
    public function remove_tabs($tabs)
    {
         return [];
    }

    // Add custom role if not exists
    public function register_role()
    {
        $role = get_option('local_role_exists');

        if( !$role ) {
            add_role(
                'local',
                __( 'Lokalanvändare' ),
                [
                    'read'                   => true,  
                    'edit_pages'             => true,
                    'read_page'              => true,
                    'publish_pages'          => true,
                    'edit_published_pages'   => true,
                    'delete_published_pages' => true,
                    'publish_pages'          => true,
                    'publish_pages'          => true,
                    'delete_page'            => true, 
                ]
            );

            update_option('local_role_exists', true);
        }
    }


    // Give user permission
    public function user_has_cap($caps)
    {
        global $post;
       
        if( $post && $this->user_can_edit( $this->user_id, $post->ID ) ) {
            $caps['edit_others_pages']   = true;
            $caps['delete_others_pages'] = true;
        }

        return $caps;
    }


    // Bool if user can edit
    public function user_can_edit($user_id, $post_id)
    {
        $pages = $this->user_can_edit_pages($user_id);

        return in_array( $post_id, $pages );
    }


    // Return array of pages
    public function user_can_edit_pages($user_id)
    {
        $parent_id = get_user_meta($user_id, 'permission', true);
        $children  = $this->get_page_children($parent_id);
        $pages     = [$parent_id];

        foreach( $children as $child ) {
            $pages[] = $child->ID;
        }

        return $pages;
    }


    // Get child and grand child pages
    public function get_page_children($parent_id)
    {
        $children = [];

        $posts = get_posts([
            'numberposts'      => -1, 
            'post_status'      => 'publish', 
            'post_type'        => 'page', 
            'post_parent'      => $parent_id, 
            'suppress_filters' => false 
        ]);

        foreach( $posts as $child ) {
            $gchildren = $this->get_page_children($child->ID);
            
            if( !empty( $gchildren ) ) {
                $children = array_merge($children, $gchildren);
            }
        }

        return array_merge($children, $posts);
    }


    // Only include users posts
    public function pre_get_posts($query)
    {
        if( $query->is_main_query() ) {
            $user_pages = $this->user_can_edit_pages( $this->user_id );

            $query->set('post__in', $user_pages );
            $query->set('orderby', 'parent');
            $query->set('author', 0); // Hack to get all posts from any author
        }
    }


    // Include page dropdown
    public function add_page_permission_field()
    {
        include 'views/new-user-permissions.php';  
    }


    // Include page dropdown
    public function add_page_permission_field_to_profile($user)
    {
        include 'views/profile-user-permissions.php';  
    }


    // Add user page if selected
    public function save_user_permission($user_id)
    {
        $permission = isset( $_POST['permission'] ) ? $_POST['permission'] : false;

        if( $permission ) {
            update_user_meta( $user_id, 'permission', sanitize_text_field( $permission ) );
        } else {
            delete_user_meta( $user_id, 'permission' );
        }
    }
}


// Init class
add_action('admin_init', function() {
    return new Permission();
});