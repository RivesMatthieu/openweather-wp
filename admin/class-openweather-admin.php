<?php 

class Openweather_Admin {

    public function __construct() 
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );

        require_once( OPENWEATHER_PATH . 'public/class-opennweater-public.php');

    }

    public function add_plugin_page()
    {
        add_options_page(
            'Settings Admin', 
            'Config Openweather', 
            'manage_options', 
            'setting-openweather', 
            array( $this, 'create_admin_page' )
        );
    }

    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'openweather' );
        ?>
        <div class="wrap">
            <h1>Configuration de l'api openweather</h1>
            <form method="post" action="options.php">
            <?php
                settings_fields( 'openweather_group' );
                do_settings_sections( 'setting-openweather' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init()
    {
        register_setting(
            'openweather_group', // Option group
            'openweather', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Clé api', // Title
            array( $this, 'print_section_info' ), // Callback
            'setting-openweather' // Page
        );  

        add_settings_field(
            'openweather_identifiant', // ID
            'Clé api', // Title 
            array( $this, 'identifiant_callback' ), // Callback
            'setting-openweather', // Page
            'setting_section_id' // Section
        );
    }

    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['openweather_identifiant'] ) )
            $new_input['openweather_identifiant'] = sanitize_text_field( $input['openweather_identifiant'] );
            
        return $new_input;
    }

    public function print_section_info()
    {
        print 'Veuillez remplir les informations d\'accès à Openweather:';
    }

    public function identifiant_callback()
    {
        printf(
            '<input style="width: 300px;" type="text" id="openweather_identifiant" name="openweather[openweather_identifiant]" value="%s" />',
            isset( $this->options['openweather_identifiant'] ) ? esc_attr( $this->options['openweather_identifiant']) : ''
        );
    }

}

