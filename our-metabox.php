<?php

/**
 * Plugin Name:       Our Metabox
 * Plugin URI:        https://sakibmd.xyz/
 * Description:       Metabox Api Demo
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Sakib Mohammed
 * Author URI:        https://sakibmd.xyz/
 * License:           GPL v2 or later
 * License URI:
 * Text Domain:       our-metabox
 * Domain Path:       /languages
 */

class OurMetabox
{
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'omb_load_textdomain'));
        add_action('admin_menu', array($this, 'omb_add_metabox'));
        add_action('save_post', array($this, 'omb_save_metabox'));
        add_filter('the_content', array($this, 'omb_show_adding_metabox_value_with_content'));
        add_filter('user_contactmethods', array($this, 'omb_user_contact_methods')); //add additional into to user profile
    }

    public function omb_user_contact_methods($methods)
    {
        $methods['facebook'] = __('Facebook', 'our-metabox');
        return $methods;
    }

    public function omb_show_adding_metabox_value_with_content($content)
    {
        $location = get_post_meta(get_the_ID(), 'omb_location', true);
        $country = get_post_meta(get_the_ID(), 'omb_country', true);
        $checkbox_output = get_post_meta(get_the_ID(), 'omb_clr', true);
        $radio_output = get_post_meta(get_the_ID(), 'omb_color', true);
        $dropdown_output = get_post_meta(get_the_ID(), 'omb_color_dropdown', true);

        $checkbox_output_result = is_array($checkbox_output) ? $checkbox_output : array();

        $fblink = esc_url(get_the_author_meta('facebook'));
        $content .= "<br>Author Facebook Link: " . $fblink;

        if ($location == '' && $country == '' && $checkbox_output_result == array() && $radio_output == '' && $dropdown_output == 'select any') {
            return $content;
        }

        $output = "<br><strong>Location:</strong> " . $location . "<br><strong>Country:</strong> " . $country;
        $content .= $output;

        return $content;
    }

    private function is_secured($nonce_field_name, $action, $post_id)
    {
        $nonce_value = isset($_POST[$nonce_field_name]) ? $_POST[$nonce_field_name] : '';

        if ($nonce_value == '') {
            return false;
        }
        if (!wp_verify_nonce($nonce_value, $action)) {
            return false;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return false;
        }

        if (wp_is_post_autosave($post_id)) {
            return false;
        }

        if (wp_is_post_revision($post_id)) {
            return false;
        }

        return true;

    }

    public function omb_save_metabox($post_id)
    {

        if (!$this->is_secured('omb_nonce_field_name', 'omb_nonce_action', $post_id)) {
            return $post_id;
        }

        $location = isset($_POST['omb_location']) ? $_POST['omb_location'] : '';
        $country = isset($_POST['omb_country']) ? $_POST['omb_country'] : '';
        $is_favorite = isset($_POST['omb_is_favorite']) ? $_POST['omb_is_favorite'] : 0;
        $colors_checkbox = isset($_POST['omb_clr']) ? $_POST['omb_clr'] : array();
        $color_radio = isset($_POST['omb_color']) ? $_POST['omb_color'] : '';
        $omb_color_dropdown = isset($_POST['omb_color_dropdown']) ? $_POST['omb_color_dropdown'] : '';

        // if ($location == '' || $country == '') {
        //     return $post_id;
        // }

        $location = sanitize_text_field($location);
        $country = sanitize_text_field($country);

        update_post_meta($post_id, 'omb_location', $location);
        update_post_meta($post_id, 'omb_country', $country);
        update_post_meta($post_id, 'omb_is_favorite', $is_favorite);
        update_post_meta($post_id, 'omb_clr', $colors_checkbox);
        update_post_meta($post_id, 'omb_color', $color_radio);
        update_post_meta($post_id, 'omb_color_dropdown', $omb_color_dropdown);

    }

    public function omb_add_metabox()
    {
        add_meta_box(
            'omb_post_location', //id
            __('Location Info', 'our-metabox'), //title
            array($this, 'omb_display_metabox'), //display function
            'post' //array('post', 'page'),
        );
    }

    public function omb_display_metabox($post)
    {
        $location = get_post_meta($post->ID, 'omb_location', true);
        $country = get_post_meta($post->ID, 'omb_country', true);

        $is_favorite = get_post_meta($post->ID, 'omb_is_favorite', true);
        $checked = $is_favorite == 1 ? 'checked' : '';

        $saved_colors = get_post_meta($post->ID, 'omb_clr', true);
        $saved_colors = is_array($saved_colors) ? $saved_colors : array();

        // $saved_colors = [];
        // if (metadata_exists('post', $post->ID, 'omb_clr')) {
        //     $saved_colors = get_post_meta($post->ID, 'omb_clr', true);
        // }

        $saved_color = get_post_meta($post->ID, 'omb_color', true); //for radio button
        $color_for_dropdown = get_post_meta($post->ID, 'omb_color_dropdown', true); //for dropdown

        $label1 = __('Location', 'our-metabox');
        $label2 = __('Country', 'our-metabox');
        $label3 = __('Is Favorite', 'our-metabox');
        $label4 = __('Colors Checkbox', 'our-metabox');
        $label5 = __('Colors Radio', 'our-metabox');
        $label6 = __('Colors Dropdown', 'our-metabox');

        $colors = array('red', 'green', 'blue', 'yellow', 'magenta', 'pink', 'black');

        wp_nonce_field('omb_nonce_action', 'omb_nonce_field_name'); //action, name
        $metabox_html = <<<EOD
<p>
<label for="omb_location">{$label1}: </label>
<input type="text" name="omb_location" id="omb_location" value="{$location}" />
<br/>
<label for="omb_country">{$label2}: </label>
<input type="text" name="omb_country" id="omb_country" value="{$country}"/>
</p>
<p>
<label for="omb_is_favorite">{$label3}: </label>
<input type="checkbox" name="omb_is_favorite" id="omb_is_favorite" value="1" {$checked} />
</p>
<p>
<label>{$label4}: </label>
EOD;

        foreach ($colors as $color) {
            $_color = ucwords($color);
            $checked = in_array($color, $saved_colors) ? 'checked' : "";

            $metabox_html .= <<<EOD
<br/>
<input type="checkbox" name="omb_clr[]" id="omb_clr_{$color}" value="{$color}" {$checked}  />
<label for="omb_clr_{$color}">{$_color}</label>
EOD;
        }

        $metabox_html .= "</p>";

        $metabox_html .= <<<EOD
        <p>
        <label>{$label5}: </label>
        EOD;

        foreach ($colors as $color) {
            $_color = ucwords($color);
            $checked = ($color == $saved_color) ? "checked='checked'" : '';
            $metabox_html .= <<<EOD
        <br/>
        <input type="radio" name="omb_color" id="omb_color_{$color}" value="{$color}" {$checked}  />
        <label for="omb_color_{$color}">{$_color}</label>
        EOD;
        }

        $metabox_html .= "</p>";

        $metabox_html .= <<<EOD
                <p>
                <label>{$label6}: </label>
                <select name="omb_color_dropdown" id="omb_color_dropdown">
                EOD;

        $list_for_dropdown = $colors;
        array_unshift($list_for_dropdown, 'select any');
        //print_r($list_for_dropdown);
        foreach ($list_for_dropdown as $color) {
            $selected = ($color == $color_for_dropdown) ? "selected" : '';
            $metabox_html .= <<<EOD

                <option value="{$color}" {$selected}>{$color}</option>



                EOD;
        }

        $metabox_html .= "</select></p>";

        echo $metabox_html;

    }

    public function omb_load_textdomain()
    {
        load_plugin_textdomain('our-metabox', false, dirname(__FILE__) . "/languages");
    }
}

new OurMetabox();

/*
<select name="cars" id="cars">
<option value="volvo">Volvo</option>
<option value="saab">Saab</option>
<option value="mercedes">Mercedes</option>
<option value="audi">Audi</option>
</select>
 */
