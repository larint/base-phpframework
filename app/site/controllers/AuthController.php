<?php

class AuthController extends BaseController 
{
	public function __construct() {
		parent::__construct();
		// $this->admins = new Admins; // khởi tạo model
	}

	private function login()
    {
        $this->view->render('auth.login');
    }
    
	private function doLogin( $request ) {
		$username = $_POST['username'];
		$password = $_POST['password'];

		if (empty($username) || empty($password)) {
			Router::redirectBack(['error' => ['Vui lòng nhập đầy đủ thông tin!']]);
		}   

		$user = $this->admins->findOnWhereAdmin($username);

		// kiểm tra số lần đăng nhập thất bại <=5 thì check đăng nhập
		if ( !empty($user) && $user->ad_login_fail <= 5 && password_verify(md5($password), $user->ad_password ) ) {
			if ( $user->ad_login_fail > 0 ) {
				$this->admins->resetLoginFails($username);
			}

			// luu session thong tin user dang nhap
			$userLogin = $this->admins->findOnWhereAdminNoPass($username);
			session::setUser($userLogin);

			Router::redirect('dashboard');
		} else if ( $user->ad_login_fail >= 5 ) {
			$this->admins->increaseLoginFails($username);
			if ( $user->ad_login_fail == 9) {
				$this->admins->resetLoginFails($username);
			}
			Router::redirectBack(['error' => ['Bạn đã đăng nhập sai quá 5 lần']]);
		} else {
			if ( !empty($user) ) { // kiểm tra nếu tồn tại username này thì tăng loginfail lên
				$this->admins->increaseLoginFails($username);
			}
			Router::redirectBack(['error' => ['Thông tin đăng nhập không đúng']]);
		}
		
	}

	public function getRegistry($request)
    {
        $params = $request;   
        $this->view->render('pages.signup', compact('params'));
    }

	private function doRegistry() {
		AppRouter::redirectBack(['error' => ['Thông tin đăng nhập không đúng']]);
	}

	private function doLogout() {
		session::removeUser();
		Router::redirect('loginAdmin');
	}

	public static function isLoginAmin() {
		return session::has('user') ? true : false;
	}

	public function __call($method, $args) {
        // $actionNoAuth mảng các action không cần xác thực
        $actionNoAuth = ['login','doLogin'];
        if ( !self::isLoginAmin() ) { 
            if( !in_array($method, $actionNoAuth) ) Router::redirect('loginAdmin');
        } else if ($method == 'login'){
        	Router::redirect('dashboard');
        }
        call_user_func_array([$this, $method], $args);
    }

	// hàm để sinh một mật khẩu
	public function genPass($pass) {
		$hash_pass = hashPass($pass);
		error_log($pass. ' : ' .$hash_pass . PHP_EOL, 3, 'hash_pass');
	}
}