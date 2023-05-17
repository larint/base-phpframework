<?php

class ValidateRequest
{
    public function __construct($args)
    {
        foreach($args as $key => $value) {
            if ($value == "true") {
                $this->$key = true;
            } elseif ($value == "false") {
                $this->$key = false;
            } else {
                $this->$key = $value;
            }
        }
    }

    public function validate($rules = array(), $redirect = false)
    {
        $error = [];
        foreach ($rules as $param => $rule) {
            $ruleArr = explode('|', $rule);
            foreach ($ruleArr as $ruleHandle) {
                $handle = explode(':', $ruleHandle);
                $extend = isset($handle[1]) ? $handle[1] : '';
                $func = 'check' . ucfirst($handle[0]);
                $check = $this->{$func}($param, $extend);
                if (!$check) {
                    if ($redirect) {
                        redirect_back(['error' => $this->error]);
                    } else {
                        return $this->error;
                    }
                }
            }
        }
        return true;
    }

    private function checkRequired($param, $extend ='')
    {
        $value = $this->$param;
        if (empty($value)) {
            $this->error[$param] = lang('validate.required', ['field' => $param]);
            return false;
        }
        return true;
    }

    private function checkMin($param, $minSize ='')
    {
        $value = $this->$param;
        if (strlen($value) < $minSize) {
            $this->error[$param] = lang('validate.min', ['field' => $param, 'min' => $minSize]);
            return false;
        }
        return true;
    }

    private function checkMax($param, $mazSize ='')
    {
        $value = $this->$param;
        if (strlen($value) > $mazSize) {
            $this->error[$param] = lang('validate.max', ['field' => $param, 'max' => $mazSize]);
            return false;
        }
        return true;
    }

    private function checkEmail($param, $extend ='')
    {
        $value = $this->$param;
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->error[$param] = lang('validate.email');
            return false;
        }
        return true;
    }

    private function checkString($param, $extend ='')
    {
        $value = $this->$param;
        if (preg_match("/^[a-zA-Z]+$/", $value) != 1) {
            $this->error[$param] = lang('validate.string', ['field' => $param]);
            return false;
        }
        return true;
    }

    private function checkNumber($param, $extend ='')
    {
        $value = $this->$param;
        if (preg_match("/^[0-9]+$/", $value) != 1) {
            $this->error[$param] = lang('validate.number', ['field' => $param]);
            return false;
        }
        return true;
    }

    private function checkUrl($param, $extend ='')
    {
        $value = $this->$param;
        if (preg_match("/[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&=]*)/", $value) != 1) {
            $this->error[$param] = lang('validate.url', ['field' => $param]);
            return false;
        }
        return true;
    }

    private function checkUnique($param, $extend ='')
    {
        $value = $this->$param;
        if (empty($extend)) {
            throw new Exception("Syntax must be unique:table_name.column_name");
        }
        $extend = explode('.', $extend);
        $table = $extend[0];
        $col = $extend[1];
        $model = new Model();
        $result = $model->select(['id'], '', $table)
                            ->where([
                                $col => $value
                            ])
                            ->first();
        if ($result) {
            $this->error[$param] = lang('validate.unique', ['field' => $param]);
            return false;
        }
        return true;
    }

}
