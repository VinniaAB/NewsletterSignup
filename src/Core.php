<?php
/**
 * Created by PhpStorm.
 * User: joakimcarlsten
 * Date: 19/09/16
 * Time: 17:19
 */

namespace NewsletterSignup;


use DrewM\MailChimp\MailChimp;

class Core
{
    private $pluginUrl;
    /**
     * @var MailChimp
     */
    private $mailchimpClient;

    private $subscriptionId;
    /**
     * @var Settings
     */
    private $settings;


    /**
     * Core constructor.
     */
    public function __construct($pluginUrl, MailChimp $mailchimpClient, $subscriptionId, Settings $settings)
    {

        $this->pluginUrl = $pluginUrl;
        $this->mailchimpClient = $mailchimpClient;
        $this->subscriptionId = $subscriptionId;
        $this->settings = $settings;
    }

    public function run()
    {
        add_action(
            'wp_enqueue_scripts',
            function() {
                $this->addScripts();
            }
        );

        add_action(
            'wp_ajax_subscriberSubmit',
            function () {
                $this->subscribeRequest();
            }
        );
        add_action(
            'wp_ajax_nopriv_subscriberSubmit',
            function () {
                $this->subscribeRequest();
            }
        );
    }

    private function addScripts()
    {
        wp_enqueue_script('newsletter-js',
            $this->pluginUrl.'src/assets/js/newsletter.js',
            [
                'jquery'
            ],
            false,
            true
        );

        /*wp_enqueue_script('bootstrap-modal',
            $this->pluginUrl.'src/assets/vendor/bootstrap/js/modal.min.js',
            ['jquery'],
            false,
            true
        );*/

        $localize_array = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'supply_email' => __('Please supply an email address', 'newsletter'),
        ];

        wp_localize_script('newsletter-js', 'ajax_object',
            $localize_array
        );

        /*wp_enqueue_style(
            'newsletter-style',
            $this->pluginUrl.'src/assets/css/modals.min.css'
        );*/
    }

    private function subscribeRequest()
    {
        // Make the call

        $result = $this->mailchimpClient->post("lists/$this->subscriptionId/members", array(
            'email_address' => $_POST['email'],
            'status' => 'subscribed',
            'merge_fields' => ['FNAME'=>'First name', 'LNAME'=>'Last name'],
            'double_optin' => false,
            'update_existing' => true,
            'replace_interests' => false,
            'send_welcome' => false,
        ));

        if ($this->mailchimpClient->success()) {
            $modal_title = $this->settings->getSuccessTitle();
            $modal_content = $this->settings->getSuccessMessage();
        } else {
            $modal_title = $this->settings->getFailedTitle();
            $modal_content = sprintf(
                $this->settings->getFailedMessage(),
                $this->mailchimpClient->getLastError()
            );
        }

        $templateLoader = new TemplateLoader();
        //get html for modal
        ob_start();
        $templateLoader->getTemplate('modal.php',
            [
                'modal_title' => $modal_title,
                'modal_content' => $modal_content
            ]
        );

        $modal_html = ob_get_clean();

        // Set JSON headers
        header('Content-Type: application/json');

        // Echo response
        if ($this->mailchimpClient->success()) {
            echo wp_json_encode(array('success' => true, 'modal' => $modal_html));
        } else {
            echo wp_json_encode(array('success' => false, 'modal' => $modal_html));
        }

        die();
    }

}