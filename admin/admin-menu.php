<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'qbb_create_admin_menu');

function qbb_create_admin_menu() {
    add_menu_page(
        'Quick Buy Plugin',
        'Quick Buy',
        'manage_options',
        'qbb-settings',
        'qbb_settings_page',
        'dashicons-cart',
        56
    );
}

function qbb_settings_page() {
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
    ?>
    <div class="wrap">
        <h1>Quick Buy</h1>

        <nav class="nav-tab-wrapper">
            <a href="?page=qbb-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
            <a href="?page=qbb-settings&tab=design" class="nav-tab <?php echo $active_tab == 'design' ? 'nav-tab-active' : ''; ?>">Design</a>
            <a href="?page=qbb-settings&tab=integration" class="nav-tab <?php echo $active_tab == 'integration' ? 'nav-tab-active' : ''; ?>">KeyCRM</a>
        </nav>

        <form method="post" action="options.php">
            <?php
            if ($active_tab == 'general') {
                settings_fields('qbb_general_options');
                do_settings_sections('qbb_general');
            } elseif ($active_tab == 'design') {
                settings_fields('qbb_design_options');
                do_settings_sections('qbb_design');
            }
             elseif ($active_tab == 'integration') {
                settings_fields('qbb_integration_options');
                do_settings_sections('qbb_integration');
            }
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'qbb_register_settings');

function qbb_register_settings() {
    register_setting('qbb_general_options', 'qbb_use_default_class');
    register_setting('qbb_general_options', 'qbb_custom_class');
    register_setting('qbb_general_options', 'qbb_use_integration');

    add_settings_section('qbb_main_section', 'Basic settings', null, 'qbb_general');

    add_settings_field(
        'qbb_use_default_class',
        'Use standard class?',
        'qbb_use_default_class_callback',
        'qbb_general',
        'qbb_main_section'
    );

    add_settings_field(
        'qbb_custom_class',
        'Custom buy button class',
        'qbb_custom_class_callback',
        'qbb_general',
        'qbb_main_section'
    );

    register_setting('qbb_design_options', 'qbb_button_color');
    register_setting('qbb_design_options', 'qbb_button_text_color');
    register_setting('qbb_design_options', 'qbb_button_custom_text');

    add_settings_section('qbb_design_section', 'Customize button styles', null, 'qbb_design');

    add_settings_field(
        'qbb_button_color',
        'Button color',
        'qbb_button_color_callback',
        'qbb_design',
        'qbb_design_section'
    );

    add_settings_field(
        'qbb_button_text_color',
        'Button text color',
        'qbb_button_text_color_callback',
        'qbb_design',
        'qbb_design_section'
    );
    
    add_settings_field(
        'qbb_button_custom_text',
        'Button text',
        'qbb_button_custom_text_callback',
        'qbb_design',
        'qbb_design_section'
    );
    
    register_setting('qbb_integration_options', 'qbb_use_integration');
    register_setting('qbb_integration_options', 'qbb_keycrm_key');
    register_setting('qbb_integration_options', 'qbb_keycrm_source');

    add_settings_section('qbb_integration_section', 'KeyCRM settings', null, 'qbb_integration');
    

    add_settings_field(
        'qbb_use_integration',
        'Use KeyCRM?',
        'qbb_use_integration_callback',
        'qbb_integration',
        'qbb_integration_section'
    );
    
    add_settings_field(
        'qbb_keycrm_key',
        'KeyCRM key',
        'qbb_keycrm_key_callback',
        'qbb_integration',
        'qbb_integration_section'
    );
    
    add_settings_field(
        'qbb_keycrm_key',
        'KeyCRM key',
        'qbb_keycrm_key_callback',
        'qbb_integration',
        'qbb_integration_section'
    );
    
    add_settings_field(
        'qbb_keycrm_source',
        'KeyCRM source',
        'qbb_keycrm_source_callback',
        'qbb_integration',
        'qbb_integration_section'
    );

}


function qbb_use_default_class_callback() {
    $checked = get_option('qbb_use_default_class', 'yes') === 'yes' ? 'checked' : '';
    echo "<input type='checkbox' name='qbb_use_default_class' value='yes' $checked> Use standard class '.single_add_to_cart_button'";
}

function qbb_custom_class_callback() {
    $value = get_option('qbb_custom_class', '');
    echo "<input type='text' name='qbb_custom_class' value='$value' placeholder='.my-custom-btn'>";
}

function qbb_button_color_callback() {
    $value = get_option('qbb_button_color', '#ff5722');
    echo "<input type='color' name='qbb_button_color' value='$value'>";
}

function qbb_button_text_color_callback() {
    $value = get_option('qbb_button_text_color', '#ffffff');
    echo "<input type='color' name='qbb_button_text_color' value='$value'>";
}

function qbb_button_custom_text_callback() {
    $value = get_option('qbb_button_custom_text', '');
    echo "<input type='text' name='qbb_button_custom_text' value='$value' placeholder='In 1 click'>";
}

function qbb_use_integration_callback() {
    $checked = get_option('qbb_use_integration', 'yes') === 'yes' ? 'checked' : '';
    echo "<input type='checkbox' name='qbb_use_integration' value='yes' $checked> Use Lead Sending in KeyCRM?";
}

function qbb_keycrm_key_callback() {
    $value = get_option('qbb_keycrm_key', '');
    echo "<input type='text' name='qbb_keycrm_key' value='$value' placeholder='key'>";
}

function qbb_keycrm_source_callback() {
    $value = get_option('qbb_keycrm_source', '');
    echo "<input type='text' name='qbb_keycrm_source' value='$value' placeholder='source ID'>";
}

?>