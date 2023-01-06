<?php

class AdventCalendarVouchergeneratorModuleFrontController extends ModuleFrontController {
    private $promo = null;
    private $code = null;
    private $error = null;

    public function __construct() {
        parent::__construct();
    }

    public function postProcess() {
        $promo = '';
        $dayToday = date("d");
        $tomorrowdate = new DateTime('tomorrow');
        $tomorrow = $tomorrowdate->format("Y-m-d H:i:s");
        if(Tools::isSubmit('submit')) {
            if($this->context->customer->isLogged()) {
                $min = Configuration::get('ADVENTCALENDAR_'.$dayToday.'_MIN');
                $max = Configuration::get('ADVENTCALENDAR_'.$dayToday.'_MAX');
                $this->promo = random_int($min, $max);
                $id_customer = $this->context->customer->id;
                $email_customer = $this->context->customer->email;
                $customer = explode("@", $email_customer);
                $email = $customer[0];
                $today = date("Y-m-d H:i:s");
                $this->code = $email . $this->promo;
                $promo = $this->promo;
                $code = $this->code;
                $users_list = Configuration::get('ADVENTCALENDAR_USERS');
                $users = explode(" ", $users_list);
                if(in_array($dayToday, $users)) {
                    $this->addPromo($email, $users, $today, $tomorrow, $id_customer, $promo, $code, $users_list, $dayToday);
                } else {
                    Configuration::updateValue('ADVENTCALENDAR_USERS', "");
                    $users_list = Configuration::get('ADVENTCALENDAR_USERS');
                    $users = explode(" ", $users_list);
                    $this->addPromo($email, $users, $today, $tomorrow, $id_customer, $promo, $code, $users_list, $dayToday);
                }
            }
        }
    }
    public function initContent() {
        parent::initContent();

        $tpl_vars = [
            'min' => Configuration::get('ADVENTCALENDAR_MIN'),
            'max' => Configuration::get('ADVENTCALENDAR_MAX'),
            'link' => Context::getContext()->link->getModuleLink('adventcalendar', 'vouchergenerator'),
            'promo' => $this->promo,
            'code' => $this->code,
            'error' => $this->error,
            'calendar' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24],
            'today' => date("d"),
            'month' => date("m"),
        ];
        $this->context->smarty->assign($tpl_vars);
        $this->setTemplate('module:adventcalendar/template/views/front/front.tpl');
    }

    public function addPromo($email, $users, $today, $tomorrow, $id_customer, $promo, $code, $users_list, $dayToday) {
        if(in_array($email, $users)) {
            $this->error = "Calendrier de l'avent déjà ouvert";
            $this->promo = null;
            $this->code = null;
        } else {
            $this->error = null;
            Configuration::updateValue('ADVENTCALENDAR_USERS', $users_list . " " . $dayToday . " " . $email);
            $coupon = new CartRule();
            $coupon->name = array(1 => "avent-".$dayToday."-".$promo, 2 => "avent-".$dayToday."-".$promo);
            $coupon->description = $this->context->customer->email." ".$promo."%";
            $coupon->date_from = $today;
            $coupon->date_to = $tomorrow;
            $coupon->quantity = 1;
            $coupon->quantity_per_user = 1;
            $coupon->active = 1;
            $coupon->id_customer = $id_customer;
            $coupon->code = $code;
            $coupon->highlight = true;
            $coupon->reduction_percent = $promo;
            $coupon->minimum_amount = 0;
            $coupon->minimum_amount_tax = 0;
            $coupon->minimum_amount_currency = 1;
            $coupon->minimum_amount_shipping = 0;
            $coupon->add();
        }
    }
}