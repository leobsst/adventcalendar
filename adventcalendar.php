<?php

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class AdventCalendar extends Module implements WidgetInterface {
    private $templateFile;

    public function __construct() {
        $this->name = 'adventcalendar';
        $this->version = '1.0.0';
        $this->author = 'LEOBSST';
        $this->need_instance = 1;
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Calendrier de l\'avent', [], 'Modules.AdventCalendar.Admin');
        $this->description = $this->trans('', [], 'Modules.AdventCalendar.Admin');
        $this->confirmUninstall = $this->trans('Vous désinstallez mon super module, êtes-vous sûr ?', [], 'Modules.AdventCalendar.Admin');

        $this->templateFile = 'module:adventcalendar/template/views/widget.tpl';

        $this->calendar = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24];
    }

    public function install() {
        foreach($this->calendar as $day) {
            Configuration::updateValue('ADVENTCALENDAR_'.$day.'_MIN', '0');
            Configuration::updateValue('ADVENTCALENDAR_'.$day.'_MAX', '0');
        };
        return parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('header');
    }

    public function uninstall() {
        return parent::uninstall()
            && $this->unregisterHook('displayHome');
    }

    public function hookHeader($params) {
        $this->context->controller->registerStylesheet(
            'module-adventcalendar-style',
            'modules/'.$this->name.'/template/views/assets/main.css',
            [
                'media' => 'all',
                'priority' => 200
            ]
        );
    }

    public function renderWidget($hookname, array $configuration) {
        $templateVars = $this->getWidgetVariables($hookname, $configuration);
        $this->smarty->assign($templateVars);
        return $this->fetch($this->templateFile);
    }

    public function getWidgetVariables($hookname, array $configuration) {
        return [
            'users' => Configuration::get('ADVENTCALENDAR_USERS'),
            'link' => Context::getContext()->link->getModuleLink('adventcalendar', 'vouchergenerator')
        ];
    }

    public function getContent() {
        $output = $this->post_validate();
        return $output.$this->renderForm();
    }

    public function post_validate() {
        $output = '';
        if(Tools::isSubmit('submit')) {
            foreach($this->calendar as $day) {
                if(Tools::getValue('ADVENTCALENDAR_'.$day.'_MIN') === '' || Tools::getValue('ADVENTCALENDAR_'.$day.'_MAX') === '') {
                    $output = $this->displayError('Champs obligatoires');
                    break;
                }  else  {
                    Configuration::updateValue('ADVENTCALENDAR_'.$day.'_MIN', Tools::getValue('ADVENTCALENDAR_'.$day.'_MIN'));
                    Configuration::updateValue('ADVENTCALENDAR_'.$day.'_MAX', Tools::getValue('ADVENTCALENDAR_'.$day.'_MAX'));
                    $output = $this->displayConfirmation('Les paramètres ont été enregistrés');
                }
            };
        }
        return $output;
    }

    public function renderForm() {
        $inputs = array();
        foreach($this->calendar as $day) {
            array_push($inputs,
            [
                'type' => 'text',
                'name' => 'ADVENTCALENDAR_' . $day . '_MIN',
                'cast' => 'intval',
                'label' => $this->trans('Minimum jour '.$day, [], 'Modules.AdventCalendar.Admin'),
                'required' => 1
            ],
            [
                'type' => 'text',
                'name' => 'ADVENTCALENDAR_' . $day . '_MAX',
                'cast' => 'intval',
                'label' => $this->trans('Maximum jour '.$day, [], 'Modules.AdventCalendar.Admin'),
                'required' => 1
            ]);
        }
        $field_form = [
            'form' => [
                'tinymce' => true,
                'legend' => [
                    'title' => $this->trans('Settings', [], 'Admin.Global'),
                    'icon' => 'icon-cogs',
                ],
                'description' => $this->trans('Generate a random voucher', [], 'Modules.AdventCalendar.Admin'),
                'input' => $inputs,
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ]
            ]
        ];

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldValue(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$field_form]);
    }

    public function getConfigFieldValue() {
        $values = [];
        foreach($this->calendar as $day) {
            $values['ADVENTCALENDAR_'.$day.'_MIN'] = Configuration::get('ADVENTCALENDAR_'.$day.'_MIN');
            $values['ADVENTCALENDAR_'.$day.'_MAX'] = Configuration::get('ADVENTCALENDAR_'.$day.'_MAX');
        };
        return $values;
    }
}