<?php
/**
 * Created by PhpStorm.
 * User: joakimcarlsten
 * Date: 19/09/16
 * Time: 15:13
 */

namespace NewsletterSignup\Admin;


abstract class AbstractSettingsPage
{

    protected $settings_page_properties;

    public function __construct($settings_page_properties)
    {
        $this->settings_page_properties = $settings_page_properties;
    }

    public function run()
    {
        add_action('admin_menu', function () {
            $this->add_menu_and_page();
        });
        add_action('admin_init', function () {
            $this->register_settings();
        });
    }

    public function add_menu_and_page()
    {
        add_submenu_page(
            $this->settings_page_properties['parent_slug'],
            $this->settings_page_properties['page_title'],
            $this->settings_page_properties['menu_title'],
            $this->settings_page_properties['capability'],
            $this->settings_page_properties['menu_slug'],
            array($this, 'render_settings_page')
        );

    }

    public function register_settings()
    {

        register_setting(
            $this->settings_page_properties['option_group'],
            $this->settings_page_properties['option_name']
        );

    }

    public function get_settings_data()
    {
        return wp_parse_args(get_option($this->settings_page_properties['option_name'], $this->get_default_settings_data()), $this->get_default_settings_data());
    }

    abstract public function render_settings_page();

    public function get_default_settings_data()
    {
        $defaults = [];

        return $defaults;
    }

}