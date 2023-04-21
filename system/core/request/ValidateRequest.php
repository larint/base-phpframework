<?php

class ValidateRequest
{

    public function __construct( $args )
    {
        foreach($args as $key => $value) {
            $this->$key = $value;
        }
    }

    public function validate($rules = array(), $redirect = false) {
        $error = [];
        foreach ($rules as $param => $rule) {
            $ruleArr = explode('|', $rule);
            foreach ($ruleArr as $ruleHandle) {
                $handle = explode(':', $ruleHandle);
                $extend = isset($handle[1]) ? $handle[1] : ''; 
                $check = $this->{$handle[0]}($param, $extend);
                if (!$check) {
                    if ($redirect) {
                        dd('dasd');
                        redirect_back(['error' => $this->error]);
                    } else {
                        return $this->error;
                    }
                }
            }
        }
        return true; 
    }

    private function required($param, $extend ='') {
        $value = $this->$param;
        if (empty($value)) {
            $this->error[$param] = lang('validate.required', ['field' => $param]);
            return false;
        }
        return true;
    }

    private function min($param, $minSize ='') {
        $value = $this->$param;
        if (strlen($value) < $minSize) {
            $this->error[$param] = lang('validate.min', ['field' => $param, 'min' => $minSize]);
            return false;
        }
        return true;
    }

    private function max($param, $mazSize ='') {
        $value = $this->$param;
        if (strlen($value) > $mazSize) {
            $this->error[$param] = lang('validate.max', ['field' => $param, 'max' => $mazSize]);
            return false;
        }
        return true;
    }

    private function email($param, $extend ='') {
        $value = $this->$param;
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->error[$param] = lang('validate.email');
            return false;
        }
        return true;
    }

    private function string($param, $extend ='') {
        $value = $this->$param;
        return true;
    }
}