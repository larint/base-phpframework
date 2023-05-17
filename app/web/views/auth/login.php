@extend auth.layout

@style
<style>
.login {
    width: 400px;
}
</style>
@end_style

@main_content
<div class="container login">
    <h4>Đăng nhập</h4>
    <form action="<?= route('doLogin') ?>" method="POST">
        @csrf_field
        <div class="form-group">
            <label for="exampleInputEmail1">Email address</label>
            <input type="text" value="<?= old('email') ?>" class="form-control" id="exampleInputEmail1" name="email"
                placeholder="Enter email">
            <small id="emailHelp" class="form-text text-danger"><?= error('email') ?></small>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Password</label>
            <input type="password" class="form-control" name="password" placeholder="Password">
            <small id="emailHelp" class="form-text text-danger"><?= error('password') ?></small>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="exampleCheck1" name="remember">
            <label class="form-check-label" for="exampleCheck1">Remember me</label>
        </div>
        @error
        <div class="text-right">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>
@end_main_content