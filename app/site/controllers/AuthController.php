<?php

class AuthController extends BaseController 
{
	public function __construct() {
		parent::__construct();
		$this->account = new Account; // khởi tạo model
	}

	public function getLogin()
    {
        $this->view->render('auth.login');
    }
    
	public function doLogin( $request ) {
		$error = [];
		if (empty($request->email)) {
			$error['email'] = 'Chưa nhập email!';
			redirect_back(['error' => $error]);
		}
		if (empty($request->password)) {
			$error['password'] = 'Chưa nhập password!';
			redirect_back(['error' => $error]);
		}

		$user = $this->account->select(['id','email','password'])
			->where([
				'email' => $request->email
			])->first();

		if ( $user ) {
			if ( password_verify($request->password, $user->password) ) {
				Auth::set($user, $request->remember ? true : false);
				redirect_route('home');
			}
		} 
		redirect_back(['error' => ['Đăng nhập không thành công']]);
	}

	public function getRegistry($request)
    {
        $this->view->render('pages.signup');
    }

	public function doRegistry($request) {
		$error = [];
		if (empty($request->email)) {
			$error['email'] = 'Chưa nhập email!';
		}
		if (empty($request->password)) {
			$error['password'] = 'Chưa nhập password!';
		}
		
		redirect_back(['error' => $error]);
	}

	public function doLogout() {
		Auth::unset();
		return redirect_route('getLogin');
	}

	public static function isLoginAmin() {
		return SessionApp::has('user') ? true : false;
	}

	// hàm để sinh một mật khẩu
	public function genPass($pass) {
		$hash_pass = hashPass($pass);
		error_log($pass. ' : ' .$hash_pass . PHP_EOL, 3, 'hash_pass');
	}

}