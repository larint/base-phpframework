<?php
/**
 * LOG
 */
if (!function_exists('logs')) {
    function logs($msg, $extmsg, $filename)
    {
        $msg = $extmsg . (is_array($msg) ? json_encode($msg) : $msg);
        $time = date("h:m:s d/m/Y", time());
        $path =  PATH_SYSTEM . "/storage/log";
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        error_log($time.' : '. $msg . PHP_EOL, 3, "$path/$filename");
    }
}

if (!function_exists('log_info')) {
    function log_info($msg, $extmsg = '')
    {
        logs($msg, $extmsg, 'info.log');
    }
}

if (!function_exists('log_db')) {
    function log_db($msg, $extmsg = '')
    {
        logs($msg, $extmsg, 'db.log');
    }
}

if (!function_exists('log_error')) {
    function log_error($msg, $extmsg = '')
    {
        logs($msg, $extmsg, 'error.log');
    }
}

/**
 * TOKEN
 */
if (!function_exists('gen_token')) {
    function gen_token($length = 40)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, $charactersLength - 1)];
        }
        return $token;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token($length = 40)
    {
        $csrf_token = gen_token($length);
        if (!CookieApp::getToken()) {
            CookieApp::setToken($csrf_token);
        }
        return CookieApp::getToken();
    }
}

if (!function_exists('input_hidden')) {
    function input_hidden($name, $val = '')
    {
        return '<input type="hidden" name="'.$name.'" value="'.$val.'">';
    }
}

if (!function_exists('method_field')) {
    function method_field($name)
    {
        return '<input type="hidden" name="_method" value="'.$name.'">';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        $csrf_token = csrf_token();
        return '<input type="hidden" name="_token" value="'.$csrf_token.'">';
    }
}

/**
 * TIME
 */

if (!function_exists('format_date')) {
    function format_date($date, $format)
    {
        $time = is_string($date) ? strtotime($date) : $date;
        return date($format, $time);
    }
}

if (!function_exists('current_time')) {
    function current_time($format = 'Y-m-d H:i:s')
    {
        return format_date(time(), $format);
    }
}

if (!function_exists('current_date')) {
    function current_date($format = 'Y-m-d')
    {
        return format_date(time(), $format);
    }
}

if (!function_exists('add_date')) {
    function add_date($date, $nday = 0, $format = 'Y-m-d')
    {
        return format_date("$date +$nday day", $format);
    }
}

if (!function_exists('add_currenttime')) {
    function add_currenttime($timeSecond = 3600) // default 1 hour
    {
        $part = [

            ];
        $time = time();
        if (is_numeric($timeSecond)) {
            $time += $timeSecond;
        } else {
            if (preg_match('/^(\d+)s$/m', $timeSecond, $match) && isset($match[1])) { //minute
                $time += $match[1];
            } elseif (preg_match('/^(\d+)m$/m', $timeSecond, $match) && isset($match[1])) { //minute
                $time += $match[1] * 60;
            } elseif (preg_match('/^(\d+)h$/m', $timeSecond, $match) && isset($match[1])) { //minute
                $time += $match[1] * 60 * 60;
            } elseif (preg_match('/^(\d+)d$/m', $timeSecond, $match) && isset($match[1])) { //minute
                $time += $match[1] * 24 * 60 * 60;
            } else {
                throw new Exception("Format time invalid");
            }
        }

        return date('Y-m-d H:i:s', $time);
    }
}

if (!function_exists('sub_date')) {
    function sub_date($date, $nday = 0, $format = 'Y-m-d')
    {
        return format_date("$date -$nday day", $format);
    }
}

if (!function_exists('is_login_user')) {
    function is_login_user()
    {
        return SessionApp::has('user') ? true : false;
    }
}

if (!function_exists('home_url')) {
    function home_url()
    {
        return ROOT_URL;
    }
}

if (!function_exists('ext_file')) {
    function ext_file($name)
    {
        return pathinfo($name, PATHINFO_EXTENSION);
    }
}

if (!function_exists('trim_space')) {
    function trim_space($str)
    {
        return preg_replace('/\s+/', ' ', $str);
    }
}

if (!function_exists('truncate')) {
    function truncate($string, $number_char)
    {
        if(strlen($string) <= $number_char) {
            return $string;
        }
        $str_wrap = wordwrap($string, $number_char, "::");
        return substr($string, 0, strpos($str_wrap, '::')). '...';
    }
}


if (!function_exists('is_request_admin')) {
    function is_request_admin()
    {
        $url = $_SERVER['REQUEST_URI'];
        return strpos($url, 'admin') !== false;
    }
}

if (!function_exists('GET')) {
    function GET($key)
    {
        $url = $_SERVER['REQUEST_URI'];
        $query_str = parse_url($url, PHP_URL_QUERY);
        parse_str($query_str, $query_params);
        if (isset($query_params[$key])) {
            $param = vi_to_en($query_params[$key]);
            $param = preg_replace('/[^A-Za-z0-9\- ]/', '', $param);
        } else {
            $param = '';
        }
        return isset($query_params[$key]) ? $query_params[$key] : '';
    }
}

if (!function_exists('responce_ajax_json')) {
    function responce_ajax_json($code, $data = array())
    {
        if ($code == 500) {
            $data['error'] = 'system error';
        }
        $data['code'] = $code;
        echo json_encode($data);
        exit();
    }
}

if (!function_exists('last_key_arr')) {
    function last_key_arr($array)
    {
        end($array);
        return key($array);
    }
}

if (!function_exists('contain_str')) {
    function contain_str($findme, $str)
    {
        if (empty($findme)) {
            return false;
        }
        $findme = strtolower($findme);
        $str = strtolower($str);
        return strpos($str, $findme) !== false;
    }
}

if (!function_exists('is_error_app')) {
    function is_error_app()
    {
        return empty(error_get_last()) ? false : true;
    }
}

if (!function_exists('hash_pass')) {
    function hash_pass($pass)
    {
        return password_hash(md5($pass), PASSWORD_DEFAULT);
    }
}

if (!function_exists('string_key_exists')) {
    function string_key_exists($array)
    {
        $keys = array_keys($array);
        return (count(preg_grep('/^([a-zA-Z]+)$/', $keys)) > 0) ? true : false;
    }
}

if (!function_exists('asset')) {
    function asset($path, $uniturl = false, $excludeRoot = false)
    {
        $path = (substr($path, 0, 1) == '/') ? $path : '/' . $path;
        $root = $excludeRoot ? PUBLIC_FOLDER : URL_PUBLIC;
        return $root . $path . ($uniturl ? '?t='.time() : '');
    }
}

if (!function_exists('view_path')) {
    function view_path($path)
    {
        $path = str_replace('.', '/', $path);
        return PATH_APP . "/$path.php";
    }
}

if (!function_exists('vi_to_en')) {
    function vi_to_en($str)
    {
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/', 'A', $str);
        $str = preg_replace('/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/', 'E', $str);
        $str = preg_replace('/(Ì|Í|Ị|Ỉ|Ĩ)/', 'I', $str);
        $str = preg_replace('/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/', 'O', $str);
        $str = preg_replace('/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/', 'U', $str);
        $str = preg_replace('/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/', 'Y', $str);
        $str = preg_replace('/(Đ)/', 'D', $str);
        $str = preg_replace('/(")/', '', $str);
        return $str;
    }
}

if (!function_exists('slugify')) {
    function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = vi_to_en($text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) {
            return '';
        }
        return $text;
    }
}

if (!function_exists('url_name')) {
    function url_name($url)
    {
        $url = url_short($url);
        $posDot = strpos($url, '.');
        return substr($url, 0, $posDot);
    }
}

if (!function_exists('url_short')) {
    function url_short($url)
    {
        $url = str_replace('http://', '', $url);
        $url = str_replace('https://', '', $url);
        $url = str_replace('www.', '', $url);
        return rtrim($url, '/');
    }
}

if (!function_exists('has_file_req')) {
    function has_file_req($FILES)
    {
        return $FILES[key($FILES)]['error'] == 0;
    }
}

if (!function_exists('get_server_ip')) {
    function get_server_ip()
    {
        return $_SERVER['SERVER_ADDR'];
    }
}

if (!function_exists('get_client_ip')) {
    function get_client_ip()
    {
        //$_SERVER['SERVER_ADDR']
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif(isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif(isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }
}

if (!function_exists('str_in_key_array')) {
    function str_in_key_array($arrayKey, $el)
    {
        return count(array_filter($arrayKey, create_function('$key', '$key = trim($key); return strstr("'.$el.'", $key);'))) > 0;
    }
}

if (!function_exists('is_last_slash_path')) {
    function is_last_slash_path($path)
    {
        $len = strlen($path);
        $slash = substr($path, $len-1, $len);
        return $slash == '/';
    }
}

if (!function_exists('download_file')) {
    function download_file($url, $nameFile, $pathFolderUpload, $folder = array())
    {
        if (!is_last_slash_path($pathFolderUpload)) {
            $pathFolderUpload .= '/';
        }
        foreach ($folder as $value) {
            $pathFolderUpload .= $value .'/';
            if (!file_exists($pathFolderUpload)) {
                mkdir($pathFolderUpload, 0777, true);
            }
        }

        $newfname = $pathFolderUpload . $nameFile . '.' . ext_file($url);
        $file = fopen($url, "rb");
        if ($file) {
            $newf = fopen($newfname, "wb");
            if ($newf) {
                while(!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        }
        if ($file) {
            fclose($file);
        }
        if (isset($newf) && $newf) {
            fclose($newf);
        }
        return $nameFile . '.' . ext_file($url);
    }
}

if (!function_exists('write_file')) {
    function write_file($content, $file)
    {
        if (!file_exists($file)) {
            mkdir(dirname($file), 0777, true);
        }

        $myfile = fopen($file, "w") or die("Unable to open file!");
        fwrite($myfile, $content);
        fclose($myfile);
    }
}

if (!function_exists('read_file')) {
    function read_file($file)
    {
        $myfile = fopen($file, "r") or die("Unable to open file!");
        $txt = fread($myfile, filesize($file));
        fclose($myfile);
        return $txt;
    }
}

if (!function_exists('is_digit')) {
    function is_digit($strNum)
    {
        return preg_match("/^\-{0,1}[0-9\.\,]+$/", $strNum);
    }
}

if (!function_exists('arr_to_obj')) {
    function arr_to_obj($data = array(), $forceObj = true)
    {
        if ($forceObj) {
            return json_decode(json_encode($data, JSON_FORCE_OBJECT));
        }
        return json_decode(json_encode($data));
    }
}

if (!function_exists('current_url')) {
    function current_url()
    {
        return $_SERVER['REQUEST_URI'];
    }
}

if (!function_exists('build_non_nested_recursive')) {
    function build_non_nested_recursive(array &$out, $key, array $in, $split = '.')
    {
        foreach($in as $k => $v) {
            if(is_array($v)) {
                build_non_nested_recursive($out, "$key$k$split", $v, $split);
            } else {
                $out[$key . $k] = $v;
            }
        }
    }
}

/**
 * ex: config('app.db.mysql.host')
 */
if (!function_exists('config')) {
    function config($key = '')
    {
        if ($key == '') {
            throw new Exception("key config empty", 1);
        }
        $files1 = glob(PATH_SITE . '/config/*.php');
        $files2 = glob(PATH_ADMIN . '/config/*.php');
        $files = array_merge($files1, $files2);
        $configArr = [];
        foreach ($files as $fileName) {
            $config = include $fileName;
            if (!is_array($config)) {
                throw new Exception("Config file format must return as array.");
            }
            if (!empty($config)) {
                $pref = basename($fileName, '.php');
                $configTemp = array();
                build_non_nested_recursive($configTemp, "$pref.", $config);
                $configArr = array_merge($configArr, $configTemp);
            }
        }

        return isset($configArr[$key]) ? $configArr[$key] : '';
    }
}

if (!function_exists('route')) {
    function route($alias, $params = array())
    {
        return AppRouter::name($alias, $params);
    }
}

if (!function_exists('redirect_back')) {
    function redirect_back($msg = array())
    {
        if (is_array($msg) && count($msg) > 0) {
            SessionApp::setMSG($msg);
        }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        die();
    }
}

if (!function_exists('redirect_url')) {
    function redirect_url($url, $statusCode = 303)
    {
        header('Location: ' . $url, false, $statusCode);
        die();
    }
}

if (!function_exists('redirect_route')) {
    function redirect_route($nameRoute, $msg = array(), $params = array(), $statusCode = 303)
    {
        if (is_array($msg) && count($msg) > 0) {
            SessionApp::setMSG($msg);
        }

        $route = route($nameRoute, $params);
        header('Location: ' . $route, false, $statusCode);
        die();
    }
}

if (!function_exists('error')) {
    function error($name = '')
    {
        $error = SessionApp::error();
        if ($name) {
            return isset($error[$name]) ? $error[$name] : '';
        }

        return $error ? $error : [];
    }
}

if (!function_exists('old')) {
    function old($key)
    {
        $post = SessionApp::getPostRequest();
        return isset($post[$key]) ? $post[$key] : '';
    }
}

if (!function_exists('num_dot')) {
    function num_dot($num)
    {
        return number_format($num, 0, ',', '.');
    }
}

if (!function_exists('string_dot')) {
    function string_dot($str, $pos, $char = '.')
    {
        return substr_replace($str, $char, $pos, 0);
    }
}

if (!function_exists('array_flatten')) {
    function array_flatten($array, $split = null)
    {
        if ($split) {
            $split .= '.';
        }

        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value, $split . $key));
            } else {
                $result[$split . $key] = $value;
            }
        }
        return $result;
    }
}


if (!function_exists('lang')) {
    function lang($path, $attr = array())
    {
        $langData = array_flatten($GLOBALS["lang"]);
        $text = '';
        if (isset($langData[$path])) {
            $fields = array_map(function ($v) {
                return ":$v";
            }, array_keys($attr));
            $text = count($attr) > 0 ? str_ireplace($fields, array_values($attr), $langData[$path]) : $langData[$path];
        }

        return ucfirst($text);
    }
}


if (!function_exists('_t')) {
    function _t($path, $attr = array())
    {
        return lang($path, $attr);
    }
}
