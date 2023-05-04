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
		$request->validate([
			'email' => 'required|max:20|min:1',
			'password' => 'required|min:3|max:10'
		], true);

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
		$request->validate([
			'email' => 'required|max:20|min:1|unique:account.email',
			'password' => 'required|min:3|max:10'
		], true);

		if ( $user ) {
			redirect_back(['error' => ['Email đã tồn tại']]);
		} 
		$user = $this->account->create([
			'name' => 'test',
			'email' => $request->email,
			'password_display' => $request->password,
			'password' => hash_pass($request->password),
			'is_super' => 0
		]);

		redirect_back(['Đăng ký không thành công']);
	}

	public function doLogout() {
		Auth::unset();
		return redirect_route('getLogin');
	}

}