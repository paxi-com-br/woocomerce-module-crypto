<?php

if (!defined("ABSPATH")) {
    exit;
}

class WC_PAXI_Crypto_Gateway extends WC_Payment_Gateway
{
    /**
     * @var WC_PAXI_Crypto_SDK
     */
    private $paxi_crypto_sdk;

    /**
     * @var string
     */
    public $api_key;

    /**
     * @var string
     */
    public $api_secret;

    /**
     * @var string
     */
    public $webhook_secret;

    public function __construct()
    {
        $this->paxi_crypto_sdk = new WC_PAXI_Crypto_SDK;
        $this->id = "paxi_crypto";
        $this->icon = plugins_url("/../img/paxi-logo-branco.png", __FILE__);
        $this->has_fields = false;
        $this->method_title = "PAXI - Módulo Crypto";
        $this->method_description = "Aceite pagamentos Crypto na sua loja e receba em sua conta paxi.com.br";

        $this->supports = array(
            "products"
        );

        $this->init_form_fields();

        $this->init_settings();
        $this->title = $this->get_option("title");
        $this->description = $this->get_option("description");
        $this->enabled = $this->get_option("enabled");
        $this->api_key = $this->get_option("api_key");
        $this->api_secret = $this->get_option("api_secret");
        $this->webhook_secret = $this->get_option("webhook_secret");

        add_action("woocommerce_update_options_payment_gateways_" . $this->id, array($this, "process_admin_options"));
        add_action("wp_enqueue_scripts", array($this, "payment_scripts"));
        add_action("woocommerce_api_paxi_crypto", array($this, "process_webhook"));
        add_action("woocommerce_order_details_after_order_table", array($this, "process_order_after"));
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            "enabled" => array(
                "title"       => "Ativo/Inativo",
                "label"       => "Ativar PAXI como forma de pagamento",
                "type"        => "checkbox",
                "default"     => "no"
            ),
            "title" => array(
                "title"       => "Nome",
                "type"        => "text",
                "description" => "O título que o usuário vê durante o checkout.",
                "default"     => "Crypto"
            ),
            "description" => array(
                "title"       => "Descrição",
                "type"        => "textarea",
                "description" => "A descrição que o usuário vê durante o checkout.",
                "default"     => "Pagamento fácil e rápido com Crypto!",
            ),
            "api_key" => array(
                "title"       => "Api-Key",
                "type"        => "password",
                "description" => "Obtenha sua Api-Key em https://paxi.com.br/account/api",
            ),
            "api_secret" => array(
                "title"       => "Api-Secret",
                "type"        => "password",
                "description" => "Obtenha sua Api-Secret em https://paxi.com.br/account/api",
            ),
            "webhook_secret" => array(
                "title"       => "Webhook-Secret",
                "type"        => "password",
                "description" => "Obtenha sua Webhook-Secret em https://paxi.com.br/account/api",
            )
        );
    }

    public function process_admin_options()
    {
        $this->init_settings();

        $post_data = $this->get_post_data();

        if (strlen($post_data["woocommerce_{$this->id}_api_key"]) < 6) {
            $this->add_error("Api-Key informada é inválida.");
        }

        if (strlen($post_data["woocommerce_{$this->id}_api_secret"]) < 6) {
            $this->add_error("Api-Secret informada é inválida.");
        }

        if (strlen($post_data["woocommerce_{$this->id}_webhook_secret"]) < 6) {
            $this->add_error("Webhook-Secret informada é inválida.");
        }

        if ($this->get_errors()) {
            $this->display_errors();
            return false;
        }

        parent::process_admin_options();
    }

    public function payment_fields()
    {
        if ($this->description) {
            echo wpautop(wp_kses_post($this->description));
        }
    }

    public function payment_scripts()
    {
    }

    public function validate_fields()
    {
    }

    public function process_payment($order_id)
    {
        if ($_POST["payment_method"] != $this->id) {
            return;
        }

        $order = wc_get_order($order_id);
        $result = $this->paxi_crypto_sdk->process_payment($this, $order);

        if ($result == null || $result == false) {
            return array(
                "result" => "false",
                "redirect" => ""
            );
        }

        $order->update_meta_data("paxi_crypto_id", $result["id"]);
        $order->update_meta_data("paxi_crypto_url", $result["url"]);

        $order->save();

        if ($result == null || $result == false) {
            return array(
                "result" => "false",
                "redirect" => ""
            );
        }

        global $woocommerce;
        $order->update_status("pending");
        wc_reduce_stock_levels($order_id);
        $woocommerce->cart->empty_cart();

        return array(
            "result" => "success",
            "redirect" => $result["url"]
        );
    }

    public function process_order_after($order_id)
    {
        $order = wc_get_order($order_id);

        if (
            $order->get_payment_method() != $this->id
            || $order->is_paid()
            || $order->get_status() === "processing"
        ) {
            return;
        }

        $url = $order->get_meta("paxi_crypto_url");
        if (!$url) {
            return;
        }

        echo trim('
            <section class="woocommerce-customer-details">
                <h2 class="woocommerce-column__title">Pagamento com Crypto</h2>

                <a href="'. $url .'" class="woocommerce-button wp-element-button button" style="margin-top: 0" target="_blank">
                    Pagar com crypto
                </a>
            </section>
        ');
    }

    public function process_webhook()
    {
        $this->paxi_crypto_sdk->process_webhook($this);
    }

    public function order_by_crypto_id($value)
    {
        $args = array(
            "limit" => 1,
            "meta_key" => "paxi_crypto_id",
            "meta_value" => $value,
            "meta_compare" => "="
        );

        $orders = wc_get_orders($args);

        if (!empty($orders)) {
            return $orders[0];
        }

        return false;
    }
}
