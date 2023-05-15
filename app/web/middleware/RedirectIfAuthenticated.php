<?php

class RedirectIfAuthenticated implements Middleware {

	public function handle($args) {
		
		if (Auth::user()) {
			return redirect_route('home');
		}

	}

}
