<?php
class ACUI_Import{
    function __construct(){
    }

    function show(){
        if ( !current_user_can( apply_filters( 'acui_capability', 'create_users' ) ) ) {
            wp_die( __( 'You are not allowed to see this content.', 'import-users-from-csv-with-meta' ));
        }
    
        $tab = ( isset ( $_GET['tab'] ) ) ? $_GET['tab'] : 'homepage';
        $sections = $this->get_sections_from_tab( $tab );
	    $section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'main';
    
        if( isset( $_POST ) && !empty( $_POST ) ):
            if ( !wp_verify_nonce( $_POST['security'], 'codection-security' ) ) {
                wp_die( __( 'Nonce check failed', 'import-users-from-csv-with-meta' ) ); 
            }
    
            switch ( $tab ){
                  case 'homepage':
                    ACUISettings()->save_multiple( 'import_backend', $_POST );

                    if( isset( $_POST['uploadfile'] ) && !empty( $_POST['uploadfile'] ) ){
                        $this->fileupload_process( $_POST, false );
                        return;
                    }
                  break;
    
                  case 'frontend':
                      do_action( 'acui_frontend_save_settings', $_POST );
                  break;
    
                case 'columns':
                    do_action( 'acui_columns_save_settings', $_POST );
                  break;
    
                case 'mail-options':
                    do_action( 'acui_mail_options_save_settings', $_POST );
                  break;
    
                  case 'cron':
                      do_action( 'acui_cron_save_settings', $_POST );
                  break;
              }
        endif;
        
        $this->admin_tabs( $tab );
        $this->secondary_admin_tabs( $tab, $section, $sections );
        $this->show_notices();
        
          switch ( $tab ){
              case 'homepage' :
                ACUI_Homepage::admin_gui();	
            break;
    
            case 'export' :
                ACUI_Exporter::admin_gui();	
            break;
    
            case 'frontend':
                ACUI_Frontend::admin_gui();	
            break;
    
            case 'columns':
                ACUI_Columns::admin_gui();
            break;
    
            case 'meta-keys':
                ACUI_MetaKeys::admin_gui();
            break;
    
            case 'doc':
                ACUI_Doc::message();
            break;
    
            case 'mail-options':
                ACUI_Email_Options::admin_gui();
            break;
    
            case 'cron':
                ACUI_Cron::admin_gui();
            break;
    
            case 'help':
                ACUI_Help::message();
            break;
    
            default:
                do_action( 'acui_tab_action_' . $tab, $section );
            break;
        }
    }

    function admin_tabs( $current = 'homepage' ) {
        $tabs = array( 
                'homepage' => __( 'Import', 'import-users-from-csv-with-meta' ),
                'export' => __( 'Export', 'import-users-from-csv-with-meta' ),
                'frontend' => __( 'Frontend', 'import-users-from-csv-with-meta' ), 
                'cron' => __( 'Recurring import', 'import-users-from-csv-with-meta' ),
                'cron-export' => __( 'Recurring export', 'import-users-from-csv-with-meta' ),
                'columns' => __( 'Extra profile fields', 'import-users-from-csv-with-meta' ), 
                'meta-keys' => __( 'Meta keys', 'import-users-from-csv-with-meta' ), 
                'mail-options' => __( 'Mail options', 'import-users-from-csv-with-meta' ), 
                'doc' => __( 'Documentation', 'import-users-from-csv-with-meta' ), 
                'help' => __( 'More...', 'import-users-from-csv-with-meta' )
        );
    
        $tabs = apply_filters( 'acui_tabs', $tabs );
    
        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $name ){
            $class = ( $tab == $current ) ? ' nav-tab-active' : '';

            $class = apply_filters( 'acui_tab_class', $class, $tab );            
            $href = apply_filters( 'acui_tab_href', '?page=acui&tab=' . $tab, $tab );
            $target = apply_filters( 'acui_tab_target', '_self', $tab );

            if( !function_exists( 'acui_ec_check_active' ) && $tab == 'cron-export' ){
                $name = $name .= ' (PRO)';
                $href = 'https://import-wp.com/recurring-export-addon/';
                $target = '_blank';
            }
    
            echo "<a class='nav-tab$class' href='$href' target='$target'>$name</a>";    
        }
        echo '</h2>';
    }

    static function secondary_admin_tabs( $active_tab = '', $section = '', $sections = array() ){
        if( empty( $sections ) )
            return;

        $links = array();

        foreach ( $sections as $section_id => $section_name ) {
            $tab_url = add_query_arg(
                array(
                    'page'      => 'acui',
                    'tab'       => $active_tab,
                    'section'   => $section_id,
                ),
                admin_url( 'tools.php' )
            );

            $class = ( $section === $section_id ) ? 'current' : '';
            $links[ $section_id ] = '<li class="' . esc_attr( $class ) . '"><a class="' . esc_attr( $class ) . '" href="' . esc_url( $tab_url ) . '">' . esc_html( $section_name ) . '</a><li>';
        } ?>

        <div class="wp-clearfix">
            <ul class="acui-subsubsub">
                <?php echo implode( '', $links ); ?>
            </ul>
        </div>

        <?php
    }

    function get_sections_from_tab( $tab ){
        switch ( $tab ){
            case 'homepage':
            case 'export':
            case 'frontend':
            case 'columns':
            case 'meta-keys':
            case 'doc':
            case 'mail-options':
            case 'cron':
            case 'help':
              return array();
          break;
  
          default:
              return apply_filters( 'acui_tab_section_' . $tab, array() );
          break;
      }
    }

    function show_notices(){
        $notices = ACUI_Helper::get_notices();
        foreach( $notices as $notice ){
            ?>
            <div class="notice notice-success"> 
                <p><strong><?php echo $notice; ?></strong></p>
            </div>
            <?php
        }
    }

    function try_download_file( $path_to_file ){
        if( wp_http_validate_url( $path_to_file ) ){
            if ( ! function_exists( 'download_url' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            $is_google_sheet_export_csv = ( strpos( $path_to_file, 'docs.google.com/spreadsheets' ) && strpos( $path_to_file, 'output=csv' ) ) || ( strpos( $path_to_file, 'drive.google.com' ) && strpos( $path_to_file, 'export=download' ) );

            if( pathinfo( $path_to_file, PATHINFO_EXTENSION ) != 'csv' && !$is_google_sheet_export_csv ){
                echo "<p>" . __( 'Error, the file is not a CSV', 'import-users-from-csv-with-meta' ) . "</p>";
                return false;
            }

            $path_to_file = download_url( $path_to_file );

            if( is_wp_error( $path_to_file ) ){
                echo "<p>" . sprintf( __( 'Error, problems downloading the file from the URL: %s', 'import-users-from-csv-with-meta' ), $tmp_file->get_error_message() ) . "</p>";
                return false;
            }
        }

        return $path_to_file;
    }

    function manage_file_upload( $path_to_file ){
        $path_to_file = wp_normalize_path( $path_to_file );
        $path_to_file = $this->try_download_file( $path_to_file );
        
        if( validate_file( $path_to_file ) !== 0 ){
            echo "<p>" . sprintf( __( 'Error, path to file is not well written: %s', 'import-users-from-csv-with-meta' ), $path_to_file ) . "</p>";
            echo sprintf( __( 'Reload or try <a href="%s">a new import here</a>', 'import-users-from-csv-with-meta' ), get_admin_url( null, 'tools.php?page=acui&tab=homepage' ) );
            return false;
        } 
        elseif( empty( $path_to_file ) || !file_exists ( $path_to_file ) ){
            echo "<p>" . __( 'Error, the file cannot be found', 'import-users-from-csv-with-meta' ) . ": $path_to_file</p>";
            echo sprintf( __( 'Reload or try <a href="%s">a new import here</a>', 'import-users-from-csv-with-meta' ), get_admin_url( null, 'tools.php?page=acui&tab=homepage' ) );
            return false;
        }

        return $path_to_file;
    }

    function fileupload_process( $form_data, $is_cron = false, $is_frontend  = false ) {
        if ( !defined( 'DOING_CRON' ) && ( !isset( $form_data['security'] ) || !wp_verify_nonce( $form_data['security'], 'codection-security' ) ) ){
            wp_die( __( 'Nonce check failed', 'import-users-from-csv-with-meta' ) ); 
        }

        if( empty( $_FILES['uploadfile']['name'] ) ){
            $path_to_file = $this->manage_file_upload( $form_data["path_to_file"] );

            if( $path_to_file !== false )
                $this->import_users( $path_to_file, $form_data, $is_cron, $is_frontend );        
        }else{
            $this->import_users( sanitize_text_field( $_FILES['uploadfile']['tmp_name'] ), $form_data, $is_cron, $is_frontend );
        }
    }
    
    function fileupload_process_batch_cron( $form_data, $step = 1, $initial_row = 0 ){
        $path_to_file = $this->manage_file_upload( $form_data["path_to_file"] );

        if( $path_to_file !== false )
            $this->import_users( $path_to_file, $form_data, true, false, $step, $initial_row, 29 );        
    }

    function read_first_row( $data, &$headers, &$positions, &$headers_filtered ){
        $data = apply_filters( 'pre_acui_import_header', $data );

        if( count( $data ) < 2 ){
            echo "<div id='message' class='error'>" . __( 'File must contain at least 2 columns: username and email', 'import-users-from-csv-with-meta' ) . "</div>";
            return false;
        }

        $restricted_fields = ACUIHelper()->get_restricted_fields();
        $i = 0;
        
        foreach ( $restricted_fields as $acui_restricted_field ) {
            $positions[ $acui_restricted_field ] = false;
        }

        foreach( $data as $element ){
            $headers[] = $element;

            if( in_array( strtolower( $element ) , $restricted_fields ) )
                $positions[ strtolower( $element ) ] = $i;

            if( !in_array( strtolower( $element ), $restricted_fields ) )
                $headers_filtered[] = $element;

            $i++;
        }

        update_option( "acui_columns", $headers_filtered );

        ACUIHelper()->basic_css();                        
        ACUIHelper()->print_table_header_footer( $headers );

        return true;
    }

    function prepare_array_of_data( &$data ){
        for( $i = 0; $i < count( $data ); $i++ ){
            $data[$i] = ACUIHelper()->string_conversion( $data[$i] );

            if( is_serialized( $data[$i] ) ) // serialized
                $data[$i] = @unserialize( trim( $data[$i] ), array( 'allowed_classes' => false ) );
            elseif( strpos( $data[$i], "::" ) !== false ) // list of items
                $data[$i] = ACUI_Helper::get_array_from_cell( $data[$i] );                                              
        }
    }

    function import_user( $row, $columns, $headers, $data, $positions, $form_data, $settings ){
        global $errors, $is_frontend, $is_cron;
        $data = apply_filters( 'pre_acui_import_single_user_data', $data, $headers );

        do_action( 'pre_acui_import_single_user', $headers, $data );                        

        $username = apply_filters( 'pre_acui_import_single_user_username', $data[0] );
        $data[0] = ( $username == $data[0] ) ? $username : sprintf( __( 'Converted to: %s', 'import-users-from-csv-with-meta' ), $username );
        $original_email = $data[1];
        $email = apply_filters( 'pre_acui_import_single_user_email', $data[1] );
        $data[1] = ( $email == $data[1] ) ? $email : sprintf( __( 'Converted to: %s', 'import-users-from-csv-with-meta' ), $email );

        $user_id = 0;
        
        $password_position = isset( $positions["password"] ) ? $positions["password"] : false;
        $password_changed = false;
        $password = ( $password_position === false ) ? wp_generate_password( apply_filters( 'acui_auto_password_length', 12 ), apply_filters( 'acui_auto_password_special_chars', true ), apply_filters( 'acui_auto_password_extra_special_chars', false ) ) : $data[ $password_position ];
        
        $role_position = $positions["role"];
        $role = "";
        
        $id_position = isset( $positions["id"] ) ? $positions["id"] : false;
        $id = ( empty( $id_position ) ) ? '' : $data[ $id_position ];
        
        $created = true;
        
        if( $role_position === false ){
            $role = $settings['role_default'];
        }
        else{
            $roles_cells = explode( ',', $data[ $role_position ] );
            
            if( !is_array( $roles_cells ) )
                $roles_cells = array( $roles_cells );

            array_walk( $roles_cells, 'trim' );
            
            foreach( $roles_cells as $it => $role_cell )
                $roles_cells[ $it ] = strtolower( $role_cell );
            
            $role = $roles_cells;
        }

        $no_role = ( $role == 'no_role' ) || in_array( 'no_role', $role );

        if( !$no_role ){
            if( ( !empty( $role ) || is_array( $role ) && empty( $role[0] ) ) && !empty( array_diff( $role, array_keys( wp_roles()->roles ) ) ) && $settings['update_roles_existing_users'] != 'no' ){
                if( is_array( $role ) && empty( $role[0] ) )
                    $errors[] = ACUIHelper()->new_error( $row, sprintf( __( 'If you are upgrading roles, you must choose at least one role', 'import-users-from-csv-with-meta' ), implode( ', ', $role ) ) );
                else
                    $errors[] = ACUIHelper()->new_error( $row, sprintf( __( 'Some of the next roles "%s" do not exists', 'import-users-from-csv-with-meta' ), implode( ', ', $role ) ) );
                
                return array( 'result' => 'ignored', 'user_id' => $user_id );
            }

            if ( ( !empty( $role ) || is_array( $role ) && empty( $role[0] ) ) && !empty( array_diff( $role, array_keys( ACUIHelper()->get_editable_roles() ) ) && $settings['update_roles_existing_users'] != 'no' ) ){ // users only are able to import users with a role they are allowed to edit
                $errors[] = ACUIHelper()->new_error( $row, sprintf( __( 'You do not have permission to assign some of the next roles "%s"', 'import-users-from-csv-with-meta' ), implode( ', ', $role ) ) );
                return array( 'result' => 'ignored', 'user_id' => $user_id );
            }
        }

        if( !empty( $email ) && ( ( sanitize_email( $email ) == '' ) ) ){ // if email is invalid
            $errors[] = ACUIHelper()->new_error( $row,  sprintf( __( 'Invalid email "%s"', 'import-users-from-csv-with-meta' ), $email ) );
            $data[0] = __('Invalid email','import-users-from-csv-with-meta')." ($email)";
            return array( 'result' => 'ignored', 'user_id' => $user_id );
        }
        elseif( empty( $email ) ) {
            $errors[] = ACUIHelper()->new_error( $row,  __( 'Email not specified', 'import-users-from-csv-with-meta' ) );
            $data[0] = __( 'Email not specified', 'import-users-from-csv-with-meta' );
            return array( 'result' => 'ignored', 'user_id' => $user_id );
        }

        if( !empty( $id ) ){ // if user have used id
            if( ACUIHelper()->user_id_exists( $id ) ){
                if( $settings['update_existing_users'] == 'no' ){
                    $errors[] = ACUIHelper()->new_error( $row,  sprintf( __( 'User with ID "%s" exists, we ignore it', 'import-users-from-csv-with-meta' ), $id ), 'notice' );
                    return array( 'result' => 'ignored', 'user_id' => $user_id );
                }

                // we check if username is the same than in row
                $user = get_user_by( 'ID', $id );

                if( $user->user_login == $username ){
                    $user_id = $id;
                    
                    if( $password !== "" && $settings['update_allow_update_passwords'] == 'yes' ){
                        wp_set_password( $password, $user_id );
                        $password_changed = true;
                    }

                    $new_user_id = ACUIHelper()->maybe_update_email( $user_id, $email, $password, $settings['update_emails_existing_users'], $original_email );
                    if( empty( $new_user_id ) ){
                        $errors[] = ACUIHelper()->new_error( $row,  sprintf( __( 'User with email "%s" exists, we ignore it', 'import-users-from-csv-with-meta' ), $email ), 'notice' );
                        return array( 'result' => 'ignored', 'user_id' => $user_id );
                    }
                    
                    if( is_wp_error( $new_user_id ) ){
                        $errors[] = ACUIHelper()->new_error( $row,  $new_user_id->get_error_message() );     
                        $data[0] = $new_user_id->get_error_message();
                        $created = false;
                    }
                    elseif( $new_user_id == $user_id)
                        $created = false;
                    else{
                        $user_id = $new_user_id;
                        $new_user = get_user_by( 'id', $new_user_id );
                        $data[0] = sprintf( __( 'Email has changed, new user created with username %s', 'import-users-from-csv-with-meta' ), $new_user->user_login );
                        $errors[] = ACUIHelper()->new_error( $row,  $data[0], 'notice' );
                        $created = true;
                    }
                }
                else{
                    $errors[] = ACUIHelper()->new_error( $row,  sprintf( __( 'Problems with ID "%s" username is not the same in the CSV and in database', 'import-users-from-csv-with-meta' ), $id ) );
                    return array( 'result' => 'ignored', 'user_id' => $user_id );
                }
            }
            else{
                $user_id = wp_insert_user( array(
                    'ID'		  =>  $id,
                    'user_login'  =>  $username,
                    'user_email'  =>  $email,
                    'user_pass'   =>  $password
                ) );

                $created = true;
                $password_changed = true;
            }
        }
        elseif( username_exists( $username ) ){
            $user_object = get_user_by( "login", $username );
            $user_id = $user_object->ID;

            if( $settings['update_existing_users'] == 'no' ){
                $errors[] = ACUIHelper()->new_error( $row,  sprintf( __( 'User with username "%s" exists, we ignore it', 'import-users-from-csv-with-meta' ), $username ), 'notice' );
                return array( 'result' => 'ignored', 'user_id' => $user_id );
            }
            
            if( $password !== "" && $settings['update_allow_update_passwords'] == 'yes' ){
                wp_set_password( $password, $user_id );
                $password_changed = true;
            }
            
            $new_user_id = ACUIHelper()->maybe_update_email( $user_id, $email, $password, $settings['update_emails_existing_users'], $original_email );
            if( empty( $new_user_id ) ){
                $errors[] = ACUIHelper()->new_error( $row,  sprintf( __( 'User with email "%s" exists with other username, it will be ignored', 'import-users-from-csv-with-meta' ), $email ), 'notice' );     
                return array( 'result' => 'ignored', 'user_id' => $new_user_id );
            }
            
            if( is_wp_error( $new_user_id ) ){
                $data[0] = $new_user_id->get_error_message();
                $errors[] = ACUIHelper()->new_error( $row,  $data[0] );
                $created = false;
            }
            elseif( $new_user_id == $user_id)
                $created = false;
            else{
                $user_id = $new_user_id;
                $new_user = get_user_by( 'id', $new_user_id );
                $data[0] = sprintf( __( 'Email has changed, new user created with username %s', 'import-users-from-csv-with-meta' ), $new_user->user_login );
                $errors[] = ACUIHelper()->new_error( $row,  $data[0], 'warning' );     
                $created = true;
            }
        }
        elseif( email_exists( $email ) && $settings['allow_multiple_accounts'] == "not_allowed" ){ // if the email is registered, we take the user from this and we don't allow repeated emails
            if( $settings['update_existing_users'] == 'no' ){
                $errors[] = ACUIHelper()->new_error( $row, sprintf( __( 'The email %s already exists in the system but is used by a different user than the one indicated in the CSV', 'import-users-from-csv-with-meta' ), $email ), 'warning' );
                return array( 'result' => 'ignored', 'user_id' => $user_id );
            }

            $user_object = get_user_by( "email", $email );
            $user_id = $user_object->ID;
            
            $data[0] = sprintf( __( 'User already exists as: %s (in this CSV file, it is called: %s)', 'import-users-from-csv-with-meta' ), $user_object->user_login, $username );
            $errors[] = ACUIHelper()->new_error( $row, $data[0], 'warning' );

            if( $password !== "" && $settings['update_allow_update_passwords'] == 'yes' ){
                wp_set_password( $password, $user_id );
                $password_changed = true;
            }

            $created = false;
        }
        elseif( email_exists( $email ) && $settings['allow_multiple_accounts'] == "allowed" ){ // if the email is registered and repeated emails are allowed
            // if user is new, but the password in csv is empty, generate a password for this user
            if( $password === "" ) {
                $password = wp_generate_password( apply_filters( 'acui_auto_password_length', 12 ), apply_filters( 'acui_auto_password_special_chars', true ), apply_filters( 'acui_auto_password_extra_special_chars', false ) );
            }
            
            $hacked_email = ACUI_AllowMultipleAccounts::hack_email( $email );
            $user_id = wp_create_user( $username, $password, $hacked_email );
            ACUI_AllowMultipleAccounts::hack_restore_remapped_email_address( $user_id, $email );
        }
        else{
            // if user is new, but the password in csv is empty, generate a password for this user
            if( $password === "" ) {
                $password = wp_generate_password( apply_filters( 'acui_auto_password_length', 12 ), apply_filters( 'acui_auto_password_special_chars', true ), apply_filters( 'acui_auto_password_extra_special_chars', false ) );
            }
            
            $user_id = wp_create_user( $username, $password, $email );
            $password_changed = true;
        }
        
        if( is_wp_error( $user_id ) ){ // in case the user is generating errors after this checks
            $errors[] = ACUIHelper()->new_error( $row, sprintf( __( 'Problems with user: "%s" does not exist, error: %s', 'import-users-from-csv-with-meta' ), $username, $user_id->get_error_message() ) );
            return array( 'result' => 'ignored', 'user_id' => 0 );
        }

        $user_object = new WP_User( $user_id );

        if( $created || $settings['update_roles_existing_users'] != 'no' ){
            
            if( empty( array_intersect( apply_filters( 'acui_protected_roles', array( 'administrator' ) ), ACUIHelper()->get_roles_by_user_id( $user_id ) ) ) || is_multisite() && is_super_admin( $user_id ) ){
                
                if( $settings['update_roles_existing_users'] == 'yes' || $created ){
                    $default_roles = $user_object->roles;
                    foreach ( $default_roles as $default_role ) {
                        $user_object->remove_role( $default_role );
                    }
                }

                if( !$no_role && ( $settings['update_roles_existing_users'] == 'yes' || $settings['update_roles_existing_users'] == 'yes_no_override' || $created ) ){
                    if( !empty( $role ) ){
                        if( is_array( $role ) ){
                            foreach( $role as $single_role ){
                                $user_object->add_role( $single_role );
                            }
                        }
                        else{
                            $user_object->add_role( $role );
                        }
                    }

                    $invalid_roles = array();
                    if( !empty( $role ) ){
                        if( !is_array( $role ) ){
                            $role_tmp = $role;
                            $role = array();
                            $role[] = $role_tmp;
                        }
                        
                        foreach ($role as $single_role) {
                            $single_role = strtolower($single_role);
                            if( get_role( $single_role ) ){
                                $user_object->add_role( $single_role );
                            }
                            else{
                                $invalid_roles[] = trim( $single_role );
                            }
                        }
                    }

                    if ( !empty( $invalid_roles ) ){
                        if( count( $invalid_roles ) == 1 )
                            $data[0] = __('Invalid role','import-users-from-csv-with-meta').' (' . reset( $invalid_roles ) . ')';
                        else
                            $data[0] = __('Invalid roles','import-users-from-csv-with-meta').' (' . implode( ', ', $invalid_roles ) . ')';
                
                        $errors[] = ACUIHelper()->new_error( $row, $data[0], 'warning' );  
                    }
                }
            }
        }

        // Multisite add user to current blog
        if( is_multisite() ){
            if( $created || $settings['update_roles_existing_users'] != 'no' ){
                if( empty( $role ) )
                    $role = 'subscriber';

                if( !is_array( $role ) ){
                    add_user_to_blog( get_current_blog_id(), $user_id, $role );
                }
                else{
                    foreach( $role as $single_role ){
                        $user_object->add_role( $single_role );
                    }
                }
            }
            elseif( $settings['update_roles_existing_users'] == 'no' && !is_user_member_of_blog( $user_id, get_current_blog_id() ) ){
                add_user_to_blog( get_current_blog_id(), $user_id, 'subscriber' );
            }                            
        }

        if( $columns > 2 ){
            for( $i = 2 ; $i < $columns; $i++ ):
                $data[$i] = apply_filters( 'pre_acui_import_single_user_single_data', $data[$i], $headers[$i], $i );

                if( !empty( $data ) ){
                    if( strtolower( $headers[ $i ] ) == "password" ){ // passwords -> continue
                        continue;
                    }
                    elseif( strtolower( $headers[ $i ] ) == "user_pass" ){ // hashed pass
                        if( !$created && $settings['update_allow_update_passwords'] == 'no' )
                            continue;

                        global $wpdb;
                        $wpdb->update( $wpdb->users, array( 'user_pass' => wp_slash( $data[ $i ] ) ), array( 'ID' => $user_id ) );
                        wp_cache_delete( $user_id, 'users' );
                        continue;
                    }
                    elseif( in_array( $headers[ $i ], ACUIHelper()->get_wp_users_fields() ) ){ // wp_user data									
                        if( $data[ $i ] === '' && $settings['empty_cell_action'] == "leave" ){
                            continue;
                        }
                        else{
                            wp_update_user( array( 'ID' => $user_id, $headers[ $i ] => $data[ $i ] ) );
                            continue;
                        }										
                    }
                    elseif( in_array( $headers[ $i ], ACUIHelper()->get_not_meta_fields() ) ){
                        continue;
                    }
                    else{				
                        if( $data[ $i ] === '' ){
                            if( $settings['empty_cell_action'] == "delete" )
                                delete_user_meta( $user_id, $headers[ $i ] );
                            else
                                continue;	
                        }
                        else{
                            if( is_object( $data[ $i ] ) && get_class( $data[ $i ] ) === '__PHP_Incomplete_Class' )
                                $errors[] = ACUIHelper()->new_error( $row, __( 'Invalid value __PHP_Incomplete_Class', 'import-users-from-csv-with-meta' ), 'warning' );
                            else    
                                update_user_meta( $user_id, $headers[ $i ], $data[ $i ] );
                            
                            continue;
                        }
                    }

                }
            endfor;
        }

        ACUIHelper()->print_row_imported( $row, $data, $errors );

        do_action( 'post_acui_import_single_user', $headers, $data, $user_id, $role, $positions, $form_data, $is_frontend, $is_cron, $password_changed, $created );

        $mail_for_this_user = false;
        if( $is_cron ){
            if( get_option( "acui_cron_send_mail" ) ){
                if( $created || ( !$created && get_option( "acui_cron_send_mail_updated" ) ) ){
                    $mail_for_this_user = true;
                }							
            }
        }
        else{
            if( isset( $form_data["sends_email"] ) && $form_data["sends_email"] ){
                if( $created || ( !$created && ( isset( $form_data["send_email_updated"] ) && $form_data["send_email_updated"] ) ) )
                    $mail_for_this_user = true;
            }
        }

        // wordpress default user created and edited emails
        if( get_option('acui_automatic_created_edited_wordpress_email') === 'true' ){
            ( $created ) ? do_action( 'register_new_user', $user_id ) : do_action( 'edit_user_created_user', $user_id, 'both' );
        }
            
        // send email
        $mail_for_this_user = apply_filters( 'acui_send_email_for_user', $mail_for_this_user, $headers, $data, $user_id, $role, $positions, $form_data, $is_frontend, $is_cron, $password_changed );

        if( isset( $mail_for_this_user ) && $mail_for_this_user ){
            if( !$created && $settings['update_allow_update_passwords'] == 'no' )
                $password = __( 'Password has not been changed', 'import-users-from-csv-with-meta' );

            ACUI_Email_Options::send_email( $user_object, $positions, $headers, $data, $created, $password );
        }

        return array( 'result' => ( $created ) ? 'created' : 'updated', 'user_id' => $user_id, 'role' => is_array( $role ) ? $role : array( $role ) );
    }

    function prepare_settings( $form_data ){
        global $is_cron;

        $settings = array();

        $settings['update_existing_users'] = isset( $form_data["update_existing_users"] ) ? sanitize_text_field( $form_data["update_existing_users"] ) : '';

        $role_default = isset( $form_data["role"] ) ? $form_data["role"] : array( '' );
        if( !is_array( $role_default ) )
            $role_default = array( $role_default );
        array_walk( $role_default, 'sanitize_text_field' );
        $settings['role_default'] = $role_default;
        
        $settings['update_emails_existing_users'] = isset( $form_data["update_emails_existing_users"] ) ? sanitize_text_field( $form_data["update_emails_existing_users"] ) : 'yes';
        $settings['update_roles_existing_users'] = isset( $form_data["update_roles_existing_users"] ) ? sanitize_text_field( $form_data["update_roles_existing_users"] ) : 'no';
        $settings['update_allow_update_passwords'] = isset( $form_data["update_allow_update_passwords"] ) ? sanitize_text_field( $form_data["update_allow_update_passwords"] ) : 'yes';
        $settings['empty_cell_action'] = isset( $form_data["empty_cell_action"] ) ? sanitize_text_field( $form_data["empty_cell_action"] ) : '';
        $settings['delete_users_not_present'] = isset( $form_data["delete_users_not_present"] ) ? sanitize_text_field( $form_data["delete_users_not_present"] ) : '';
        $settings['delete_users_assign_posts'] = isset( $form_data["delete_users_assign_posts"] ) ? sanitize_text_field( $form_data["delete_users_assign_posts"] ) : '';
        $settings['delete_users_only_specified_role'] = isset( $form_data["delete_users_only_specified_role"] ) ? sanitize_text_field( $form_data["delete_users_only_specified_role"] ) : false;			
        $settings['change_role_not_present'] = isset( $form_data["change_role_not_present"] ) ? sanitize_text_field( $form_data["change_role_not_present"] ) : '';
        $settings['change_role_not_present_role'] = isset( $form_data["change_role_not_present_role"] ) ? sanitize_text_field( $form_data["change_role_not_present_role"] ) : '';
        $settings['not_present_same_role'] = isset( $form_data["not_present_same_role"] ) ? sanitize_text_field( $form_data["not_present_same_role"] ) : 'no';
        
        if( $is_cron ){
            $settings['allow_multiple_accounts'] = ( get_option( "acui_cron_allow_multiple_accounts" ) == "allowed" ) ? "allowed" : "not_allowed";
        }
        else {
            $settings['allow_multiple_accounts'] = ( empty( $form_data["allow_multiple_accounts"] ) ) ? "not_allowed" : sanitize_text_field( $form_data["allow_multiple_accounts"] );
        }

        return $settings;
    }

    function time_exceeded( $time_start, $time_per_step ){
        if( $time_per_step == -1 )
            return false;

        return ( microtime( true ) - $time_start ) >= $time_per_step;
    }

    function save_transients( $columns, $headers, $headers_filtered, $positions, $errors, $errors_totals, $results, $users_created, $users_updated, $users_ignored, $roles_appeared ){
        set_transient( 'acui_columns', $columns, 15 * MINUTE_IN_SECONDS );
        set_transient( 'acui_headers', $headers, 15 * MINUTE_IN_SECONDS );
        set_transient( 'acui_headers_filtered', $headers_filtered, 15 * MINUTE_IN_SECONDS );
        set_transient( 'acui_positions', $positions, 15 * MINUTE_IN_SECONDS );
        set_transient( 'acui_errors', $errors, 15 * MINUTE_IN_SECONDS );
        set_transient( 'acui_errors_totals', $errors_totals, 15 * MINUTE_IN_SECONDS );
        set_transient( 'acui_results', $results, 15 * MINUTE_IN_SECONDS );
        set_transient( 'acui_users_created', $users_created, 15 * MINUTE_IN_SECONDS );
        set_transient( 'acui_users_updated', $users_updated, 15 * MINUTE_IN_SECONDS );
        set_transient( 'acui_users_ignored', $users_ignored, 15 * MINUTE_IN_SECONDS );
        set_transient( 'acui_roles_appeared', $roles_appeared, 15 * MINUTE_IN_SECONDS );
    }

    function import_users( $file, $form_data, $_is_cron = false, $_is_frontend = false, $step = 1, $initial_row = 0, $time_per_step = -1 ){
        $time_start = microtime( true );

        if( $time_per_step == -1 )
            @set_time_limit( 0 );

        global $errors, $errors_totals, $is_frontend, $is_cron;

        $is_frontend = $_is_frontend;
        $is_cron = $_is_cron;
        $is_backend = !$is_frontend && !$is_cron;
        $row = $initial_row;

        $settings = $this->prepare_settings( $form_data );

        @ini_set( 'auto_detect_line_endings', TRUE );
        $delimiter = ACUIHelper()->detect_delimiter( $file );

        ACUIHelper()->maybe_disable_wordpress_core_emails();

        if( $step == 1 ){
            $columns = 0;
            
            $headers = array();
            $headers_filtered = array();
            $positions = array();            

            $errors = array();
            $errors_totals = array( 'notices' => 0, 'warnings' => 0, 'errors' => 0 );
            
            $results = array( 'created' => 0, 'updated' => 0, 'deleted' => 0 );

            $users_created = array();
            $users_updated = array();
            $users_ignored = array();

            $roles_appeared = $settings['role_default'];
        }
        else{
            $columns = get_transient( 'acui_columns' );
            
            $headers = get_transient( 'acui_headers' );
            $headers_filtered = get_transient( 'acui_headers_filtered' );
            $positions = get_transient( 'acui_positions' );

            $errors = get_transient( 'acui_errors' );
            $errors_totals = get_transient( 'acui_errors_totals' );
            
            $results = get_transient( 'acui_results' );

            $users_created = get_transient( 'acui_users_created' );
            $users_updated = get_transient( 'acui_users_updated' );
            $users_ignored = get_transient( 'acui_users_ignored' );

            $roles_appeared = get_transient( 'acui_roles_appeared' );
        }

        if( $step == 1 ){
            do_action( 'before_acui_import_users' );       

            echo '<div class="wrap">';
            echo '<h2>' . apply_filters( 'acui_log_main_title', __('Importing users','import-users-from-csv-with-meta') ) . '</h2>';

            echo apply_filters( "acui_log_header", "<h3>" . __('Ready to registers','import-users-from-csv-with-meta') . "</h3>" );
            echo apply_filters( "acui_log_header_first_row_explanation", "<p>" . __('First row represents the format of the sheet','import-users-from-csv-with-meta') . "</p>" );
        }       

        $manager = new SplFileObject( $file );
        if( $initial_row != 0 )
            $manager->seek( $initial_row );

        while( $data = $manager->fgetcsv( $delimiter ) ):
            $row++;

            if( count( $data ) == 1 )
                $data = $data[0];
            
            if( $data == NULL ){
                break;
            }
            elseif( !is_array( $data ) ){
                echo apply_filters( 'acui_message_csv_file_bad_formed', __( 'CSV file seems to be badly formed. Please use LibreOffice to create and manage a CSV to be sure the format is correct', 'import-users-from-csv-with-meta') );
                break;
            }

            if( $row == 1 ){
                $columns = count( $data );
                $result = $this->read_first_row( $data, $headers, $positions, $headers_filtered );
                if( !$result )
                    break;
            }
            else{
                $this->prepare_array_of_data( $data );

                if( count( $data ) != $columns ):
                    $errors[] = ACUIHelper()->new_error( $row, __( 'Row does not have the same columns as the header, we are going to ignore this row', 'import-users-from-csv-with-meta') );
                    continue;
                endif;

                $result = $this->import_user( $row, $columns, $headers, $data, $positions, $form_data, $settings );

                if( empty( $result['role'] ) )
                    $result['role'] = array();

                $roles_appeared = array_unique( array_intersect( $roles_appeared, $result['role'] ) );

                switch( $result['result'] ){
                    case 'created':
                        $results['created']++;
                        array_push( $users_created, $result['user_id'] );
                        break;

                    case 'updated':
                        $results['updated']++;
                        array_push( $users_updated, $result['user_id'] );
                        break;

                    case 'ignored':
                        if( !empty( $result['user_id'] ) )
                            array_push( $users_ignored, $result['user_id'] );
                        break;
                }
            }

            if( $this->time_exceeded( $time_start, $time_per_step ) ){
                $this->save_transients( $columns, $headers, $headers_filtered, $positions, $errors, $errors_totals, $results, $users_created, $users_updated, $users_ignored, $roles_appeared );
                
                if( $is_cron ){
                    as_enqueue_async_action( 'acui_cron_process_step', array( 'step' => $step + 1, 'initial_row' => $row ) );
                }
                break;
            }
        endwhile;

        ACUIHelper()->print_table_end();

        ACUIHelper()->print_errors( $errors );

        ACUIHelper()->maybe_enable_wordpress_core_emails();

        // delete all users that have not been imported
        $delete_users_flag = false;
        $delete_users_assign_posts = false;
        $change_role_not_present_flag = false;

        if( $settings['delete_users_not_present'] == 'yes' ){
            $delete_users_flag = true;
            $delete_users_assign_posts = $settings['delete_users_assign_posts'];
        }

        if( $is_cron && get_option( "acui_cron_delete_users" ) ){
            $delete_users_flag = true;
            $delete_users_assign_posts = get_option( "acui_cron_delete_users_assign_posts");
        }

        if( $is_backend && $settings['change_role_not_present'] == 'yes' ){
            $change_role_not_present_flag = true;
            $change_role_not_present_role = $settings['change_role_not_present_role'];
        }

        if( $is_cron && !empty( get_option( "acui_cron_change_role_not_present" ) ) ){
            $change_role_not_present_flag = true;
            $change_role_not_present_role = get_option( "acui_cron_change_role_not_present_role");
        }

        if( $is_frontend && !empty( get_option( "acui_frontend_change_role_not_present" ) ) ){
            $change_role_not_present_flag = true;
            $change_role_not_present_role = get_option( "acui_frontend_change_role_not_present_role");
        }

        if( $errors_totals['errors'] > 0 || $errors_totals['warnings'] > 0 ){ // if there is some problem of some kind importing we won't proceed with delete or changing role to users not present to avoid problems
            $delete_users_flag = false;
            $change_role_not_present_flag = false;
        }

        $users_registered = array_merge( $users_created, $users_updated, $users_ignored );
        $users_deleted = array();

        if( $delete_users_flag ):
            $exclude_roles = array_diff( array_keys( wp_roles()->roles ), array_keys( ACUIHelper()->get_editable_roles() ) ); // remove editable roles

            if ( !in_array( 'administrator', $exclude_roles )){ // just to be sure
                $exclude_roles[] = 'administrator';
            }

            $args = array( 
                'fields' => array( 'ID' ),
                'role__not_in' => $exclude_roles,
                'exclude' => array( get_current_user_id() ), // current user never cannot be deleted
            );

            if( $settings['delete_users_only_specified_role'] || $settings['not_present_same_role'] = 'yes' ){
                $args[ 'role__in' ] = $roles_appeared;
            }

            $all_users = get_users( $args );
            $all_users_ids = array_map( function( $element ){ return intval( $element->ID ); }, $all_users );
            $users_to_remove = array_diff( $all_users_ids, $users_registered );

            $delete_users_assign_posts = ( get_userdata( $delete_users_assign_posts ) === false ) ? false : $delete_users_assign_posts;
            $results['deleted'] = count( $users_to_remove );

            foreach ( $users_to_remove as $user_id ) {
                ( empty( $delete_users_assign_posts ) ) ? wp_delete_user( $user_id ) : wp_delete_user( $user_id, $delete_users_assign_posts );
                array_push( $users_deleted, $user_id );
            }
        endif;

        if( $change_role_not_present_flag && !$delete_users_flag ):
            require_once( ABSPATH . 'wp-admin/includes/user.php');	

            $args = array( 
                'fields' => array( 'ID' ),
                'role__not_in' => $exclude_roles,
                'exclude' => array( get_current_user_id() ),
            );

            if( $settings['not_present_same_role'] = 'yes' ){
                $args[ 'role__in' ] = $roles_appeared;
            }

            $all_users = get_users( $args );
            $all_users_ids = array_map( function( $element ){ return intval( $element->ID ); }, $all_users );
            $users_to_change_role = array_diff( $all_users_ids, $users_registered );
            
            foreach ( $users_to_change_role as $user_to_change_role ) {
                $user_object = new WP_User( $user_to_change_role );
                $user_object->set_role( $change_role_not_present_role );
            }
        endif;
        
        ACUIHelper()->print_results( $results, $errors );
        
        if( !$is_frontend )
            ACUIHelper()->print_end_of_process();

        if( !$is_frontend && !$is_cron )
            ACUIHelper()->execute_datatable();

        @ini_set( 'auto_detect_line_endings', FALSE );

        set_transient( 'acui_last_import_results', array( 'created' => $users_created, 'updated' => $users_updated, 'deleted' => $users_deleted, 'ignored' => $users_ignored ) );
        do_action( 'acui_after_import_users', $users_created, $users_updated, $users_deleted, $users_ignored );
        echo '</div>';
    }
}