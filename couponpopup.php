<?php
/**
 * Coupon Popup
 *
 * Generic, configurable version: shows a coupon popup to guest visitors,
 * inviting them to create an account. Coupon code, discount label, delay and
 * re-appearance period are set from the module configuration page.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Couponpopup extends Module
{
    const CONFIG_CODE = 'COUPONPOPUP_CODE';
    const CONFIG_DISCOUNT = 'COUPONPOPUP_DISCOUNT';
    const CONFIG_DELAY = 'COUPONPOPUP_DELAY';
    const CONFIG_DAYS = 'COUPONPOPUP_REAPPEAR_DAYS';

    public function __construct()
    {
        $this->name = 'couponpopup';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Francisco Martín Hersog';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];

        parent::__construct();

        $this->displayName = $this->l('Coupon popup');
        $this->description = $this->l('Shows a popup to guest visitors offering a discount coupon when they create an account.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayBeforeBodyClosingTag')
            && Configuration::updateValue(self::CONFIG_CODE, 'WELCOME10')
            && Configuration::updateValue(self::CONFIG_DISCOUNT, '10%')
            && Configuration::updateValue(self::CONFIG_DELAY, 3)
            && Configuration::updateValue(self::CONFIG_DAYS, 2);
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName(self::CONFIG_CODE)
            && Configuration::deleteByName(self::CONFIG_DISCOUNT)
            && Configuration::deleteByName(self::CONFIG_DELAY)
            && Configuration::deleteByName(self::CONFIG_DAYS);
    }

    /**
     * Module configuration page (Modules > Coupon popup > Configure).
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitCouponpopup')) {
            $code = trim((string) Tools::getValue(self::CONFIG_CODE));
            $discount = trim((string) Tools::getValue(self::CONFIG_DISCOUNT));
            $delay = Tools::getValue(self::CONFIG_DELAY);
            $days = Tools::getValue(self::CONFIG_DAYS);

            if (!preg_match('/^[A-Za-z0-9_-]{1,32}$/', $code)) {
                $output .= $this->displayError($this->l('Invalid coupon code: use 1 to 32 letters, digits, "-" or "_".'));
            } elseif (!preg_match('/^[\p{L}\p{N}%€$£.,\- ]{1,20}$/u', $discount)) {
                $output .= $this->displayError($this->l('Invalid discount label: use up to 20 characters, e.g. "10%".'));
            } elseif (!Validate::isUnsignedInt($delay) || (int) $delay > 60) {
                $output .= $this->displayError($this->l('Invalid delay: enter a whole number of seconds between 0 and 60.'));
            } elseif (!Validate::isUnsignedInt($days) || (int) $days < 1 || (int) $days > 365) {
                $output .= $this->displayError($this->l('Invalid period: enter a whole number of days between 1 and 365.'));
            } else {
                Configuration::updateValue(self::CONFIG_CODE, $code);
                Configuration::updateValue(self::CONFIG_DISCOUNT, $discount);
                Configuration::updateValue(self::CONFIG_DELAY, (int) $delay);
                Configuration::updateValue(self::CONFIG_DAYS, (int) $days);
                $output .= $this->displayConfirmation($this->l('Settings updated.'));
            }
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $fields = [[
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Coupon code'),
                        'name' => self::CONFIG_CODE,
                        'class' => 'fixed-width-lg',
                        'required' => true,
                        'desc' => $this->l('Code shown in the popup. Create the matching cart rule in Catalog > Discounts; this module does not create it.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Discount label'),
                        'name' => self::CONFIG_DISCOUNT,
                        'class' => 'fixed-width-md',
                        'required' => true,
                        'desc' => $this->l('Text shown in the popup, e.g. 10%.'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Delay'),
                        'name' => self::CONFIG_DELAY,
                        'class' => 'fixed-width-sm',
                        'suffix' => $this->l('seconds'),
                        'desc' => $this->l('Time before the popup appears (0 to 60).'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Reappear after'),
                        'name' => self::CONFIG_DAYS,
                        'class' => 'fixed-width-sm',
                        'suffix' => $this->l('days'),
                        'desc' => $this->l('Days before the popup is shown again to a visitor who closed it (1 to 365).'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ]];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->show_toolbar = false;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitCouponpopup';
        $helper->default_form_language = (int) $this->context->language->id;
        $helper->fields_value = [
            self::CONFIG_CODE => Tools::getValue(self::CONFIG_CODE, Configuration::get(self::CONFIG_CODE)),
            self::CONFIG_DISCOUNT => Tools::getValue(self::CONFIG_DISCOUNT, Configuration::get(self::CONFIG_DISCOUNT)),
            self::CONFIG_DELAY => Tools::getValue(self::CONFIG_DELAY, Configuration::get(self::CONFIG_DELAY)),
            self::CONFIG_DAYS => Tools::getValue(self::CONFIG_DAYS, Configuration::get(self::CONFIG_DAYS)),
        ];

        return $helper->generateForm($fields);
    }

    /**
     * Only guests (not logged in) and outside the account creation page.
     */
    private function shouldDisplay()
    {
        if ($this->context->customer->isLogged()) {
            return false;
        }

        $controller = $this->context->controller;
        if (isset($controller->php_self) && $controller->php_self === 'authentication') {
            return false;
        }

        return true;
    }

    public function hookDisplayHeader($params)
    {
        if (!$this->shouldDisplay()) {
            return;
        }

        $this->context->controller->registerStylesheet(
            'couponpopup-css',
            'modules/' . $this->name . '/views/css/couponpopup.css',
            ['media' => 'all', 'priority' => 150]
        );

        $this->context->controller->registerJavascript(
            'couponpopup-js',
            'modules/' . $this->name . '/views/js/couponpopup.js',
            ['position' => 'bottom', 'priority' => 150]
        );
    }

    public function hookDisplayBeforeBodyClosingTag($params)
    {
        if (!$this->shouldDisplay()) {
            return '';
        }

        // Values are validated on save (restricted character sets), so they
        // are safe to print in the template.
        $this->context->smarty->assign([
            'couponpopup_code' => (string) Configuration::get(self::CONFIG_CODE),
            'couponpopup_discount' => (string) Configuration::get(self::CONFIG_DISCOUNT),
            'couponpopup_delay' => max(0, (int) Configuration::get(self::CONFIG_DELAY)),
            'couponpopup_days' => max(1, (int) Configuration::get(self::CONFIG_DAYS)),
        ]);

        return $this->fetch('module:couponpopup/views/templates/hook/popup.tpl');
    }
}
