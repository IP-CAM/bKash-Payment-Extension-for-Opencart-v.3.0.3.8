<?php

class ControllerExtensionPaymentBkash extends Controller
{
    private $error = array();
    public function index()
    {
        $this->language->load('extension/payment/bkash');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_bkash', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }


        $data['text_edit'] = $this->language->get('text_edit');
        $data['heading_title'] = $this->language->get('heading_title');
        $data['help_total'] = $this->language->get('help_total');
        $data['text_enabled'] = $this->language->get('text_enabled');

        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['text_all_zones'] = $this->language->get('text_all_zones');

        $data['entry_instruction'] = $this->language->get('entry_instruction');
        $data['keywords_hints'] = $this->language->get('keywords_hints');
        $data['user_token'] = $this->session->data['user_token'];


        $data['entry_total'] = $this->language->get('entry_total');

        $data['entry_order_status'] = $this->language->get('entry_order_status');

        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_total'] = $this->language->get('entry_total');

        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['button_save'] = $this->language->get('button_save');

        $data['button_cancel'] = $this->language->get('button_cancel');


        if (isset($this->error['warning'])) {

            $data['error_warning'] = $this->error['warning'];
        } else {

            $data['error_warning'] = '';
        }

        if (isset($this->error['error_instruction'])) {

            $data['error_instruction'] = $this->error['error_instruction'];
        } else {

            $data['error_instruction'] = '';
        }

        $data['breadcrumbs'] = array();


        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => false
        );


        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => ' :: '
        );


        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/bkash', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => ' :: '
        );


        $data['action'] = $this->url->link('extension/payment/bkash', 'user_token=' . $this->session->data['user_token'], 'SSL');


        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');


        if (isset($this->request->post['payment_bkash_app_key'])) {

            $data['payment_app_key'] = $this->request->post['payment_bkash_app_key'];
        } else {

            $data['payment_app_key'] = $this->config->get('payment_bkash_app_key');
        }
        if (isset($this->request->post['payment_bkash_app_secret'])) {

            $data['app_secret'] = $this->request->post['payment_bkash_app_secret'];
        } else {

            $data['payment_app_secret'] = $this->config->get('payment_bkash_app_secret');
        }
        if (isset($this->request->post['payment_bkash_username'])) {

            $data['payment_username'] = $this->request->post['payment_bkash_username'];
        } else {

            $data['payment_username'] = $this->config->get('payment_bkash_username');
        }
        if (isset($this->request->post['payment_bkash_password'])) {

            $data['payment_password'] = $this->request->post['payment_bkash_password'];
        } else {

            $data['payment_password'] = $this->config->get('payment_bkash_password');
        }

        if (isset($this->request->post['payment_bkash_grantTokenUrl'])) {

            $data['payment_grantTokenUrl'] = $this->request->post['payment_bkash_grantTokenUrl'];
        } else {

            $data['payment_grantTokenUrl'] = $this->config->get('payment_bkash_grantTokenUrl');
        }
        if (isset($this->request->post['payment_bkash_grantTokenUrl'])) {

            $data['payment_grantTokenUrl'] = $this->request->post['payment_bkash_grantTokenUrl'];
        } else {

            $data['payment_grantTokenUrl'] = $this->config->get('payment_bkash_grantTokenUrl');
        }
        if (isset($this->request->post['payment_bkash_createCheckoutUrl'])) {

            $data['payment_createCheckoutUrl'] = $this->request->post['payment_bkash_createCheckoutUrl'];
        } else {

            $data['payment_createCheckoutUrl'] = $this->config->get('payment_bkash_createCheckoutUrl');
        }
        if (isset($this->request->post['payment_bkash_createCheckoutUrl'])) {

            $data['payment_createCheckoutUrl'] = $this->request->post['payment_bkash_createCheckoutUrl'];
        } else {

            $data['payment_createCheckoutUrl'] = $this->config->get('payment_bkash_createCheckoutUrl');
        }


        if (isset($this->request->post['payment_bkash_executeCheckoutUrl'])) {

            $data['payment_executeCheckoutUrl'] = $this->request->post['payment_bkash_executeCheckoutUrl'];
        } else {

            $data['payment_executeCheckoutUrl'] = $this->config->get('payment_bkash_executeCheckoutUrl');
        }

        if (isset($this->request->post['payment_bkash_bkashscripturl'])) {

            $data['payment_bkashscripturl'] = $this->request->post['payment_bkash_bkashscripturl'];
        } else {

            $data['payment_bkashscripturl'] = $this->config->get('payment_bkash_bkashscripturl');
        }


        if (isset($this->request->post['payment_bkash_total'])) {

            $data['payment_total'] = $this->request->post['payment_bkash_total'];
        } else {

            $data['payment_total'] = $this->config->get('payment_bkash_total');
        }


        if (isset($this->request->post['bkash_order_status_id'])) {

            $data['bkash_order_status_id'] = $this->request->post['bkash_order_status_id'];
        } else {

            $data['bkash_order_status_id'] = $this->config->get('bkash_order_status_id');
        }


        $this->load->model('localisation/order_status');


        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['bkash_geo_zone_id'])) {

            $data['bkash_geo_zone_id'] = $this->request->post['bkash_geo_zone_id'];
        } else {

            $data['bkash_geo_zone_id'] = $this->config->get('bkash_geo_zone_id');
        }


        $this->load->model('localisation/geo_zone');


        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();


        if (isset($this->request->post['payment_bkash_status'])) {

            $data['payment_bkash_status'] = $this->request->post['payment_bkash_status'];
        } else {

            $data['payment_bkash_status'] = $this->config->get('payment_bkash_status');
        }


        if (isset($this->request->post['bkash_sort_order'])) {

            $data['payment_bkash_sort_order'] = $this->request->post['payment_bkash_sort_order'];
        } else {

            $data['payment_bkash_sort_order'] = $this->config->get('payment_bkash_sort_order');
        }


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/bkash', $data));
    }

    protected function validate()
    {

        if (!$this->user->hasPermission('modify', 'extension/payment/bkash')) {

            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (!$this->error) {

            return true;
        } else {

            return false;
        }
    }

}

?>