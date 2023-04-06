<?php

class AuthController extends BaseController 
{
	public function __construct() {
		parent::__construct();
		$this->account = new Account; // khởi tạo model
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
			SessionApp::setUser($userLogin);

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
		$data = $this->account->createBulk([
			[
				'name' => 'asd',
				"email" => "asdsdff@gmail.com",
				'password_display' => '12312312',
				'password' => '12312312',
				'is_super' => 2
			],
			[
				'name' => 'sd',
				"email" => "dfdf@gmail.com",
				'password_display' => '12312312',
				'password' => '12312312',
				'is_super' => 2
			]
		]);

        $this->view->render('pages.signup');
    }

	private function doRegistry($request) {
		$error = [];
		if (empty($request->email)) {
			$error['email'] = 'Chưa nhập email!';
		}
		if (empty($request->password)) {
			$error['password'] = 'Chưa nhập password!';
		}
		
		redirect_back(['error' => $error]);
	}

	private function doLogout() {
		SessionApp::removeUser();
		Router::redirect('loginAdmin');
	}

	public static function isLoginAmin() {
		return SessionApp::has('user') ? true : false;
	}

	// hàm để sinh một mật khẩu
	public function genPass($pass) {
		$hash_pass = hashPass($pass);
		error_log($pass. ' : ' .$hash_pass . PHP_EOL, 3, 'hash_pass');
	}

	public function __call($method, $args) {
        // $actionNoAuth mảng các action không cần xác thực
        $actionNoAuth = ['login','doLogin', 'doRegistry', 'getRegistry'];
        if ( !self::isLoginAmin() ) { 
            if( !in_array($method, $actionNoAuth) ) redirect_route('loginAdmin');
        } else if ($method == 'login'){
        	redirect_route('home');
        }
        call_user_func_array([$this, $method], $args);
    }
}