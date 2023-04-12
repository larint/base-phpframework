# base-phpframwork
Khung sườn php dùng để xây dựng website.

## Sử dụng tag trong php
Để include page php dùng thẻ __@include__ trong đó __partials.footer__ là đường dẫn cách nhau bằng dấu chấm
```
@include partials.footer
```
Sử dụng khai báo khối trong trang con và layout chính, cú pháp có dấu __@xxx__  ở trước. __xxx__ là tên bất kỳ.

Ví  dụ @main_content, @style được khai báo trong trong layout chính

```
<html lang="en">
    <head>
    @style
    </head>
    <body>
        @include partials.header
        <div class="container">
            @main_content
            @include partials.footer
        </div>
    </body>
</html>
```
lúc này trong trang con phải khai báo khối trong thẻ cùng tên và kết thúc bằng thẻ @end_xxx , __xxx__ trùng tên với tên khối bắt đầu.
```
@style
<style>
    html {
        font-size: 14px;
    }
</style>
@end_style

@main_content
<h1>nội dung trang con</h1>
@end_main_content
```

Sử dụng @csrf_field để chèn input token khi gửi form
```
@csrf_field
```
Một thẻ input tên token sẽ được tạo ra như dưới:
```
<input type="hidden" name="_token" value="HQf0LLhAST3CMRkYXk81o4bxNXXa92JDgvHTRKkl">
```
#### Truyền dữ liệu cho các view
khai báo các biến cần truyền đi trong hàm pass() của class ViewComposer và dùng hàm passData() để khai báo truyền biến dữ liệu,
_ví dụ như bên dưới: _
```
class ViewComposer extends ViewData
{

    public function pass()
    {
        $account = new Account;
        $role = new Role;
        $account = $account->select()->findAll();
        $role = $role->select()->findAll();

        $this->passData([
            'pages.index',  /// mảng tên view và mảng dữ liệu
            'pages.sign_up'
        ], compact('account'));

        $this->passData([
            'pages.index', 
            'pages.query_string'
        ], compact('role'));
    
    }

}
```

#### Sử dụng truy vấn select, cột khai báo không kèm toán tử thì mặc định sẽ là so sánh bằng =
##### Có thể dùng whereOr hoặc whereLike, whereLikeOr, first , last như dưới
```
$data = $this->account->select(['name', 'email', 'password_display']) 
			->where([
				'id' => 1,
				"email" => "abc@gmail.com",
                "deleted_at" => DBCRUD::IS_NULL
			]) | whereOr
			->order('id') | order('id', 'DESC') | order('id,name,email', 'DESC')
			->get(index) | first() | last(); 
```
_Câu truy vấn tương ứng:_
```
SELECT name,email,password_display FROM account WHERE id = 1 AND email = 'abc@gmail.com' AND deleted_at IS NULL ORDER BY id ASC
```
#### Sử dụng truy vấn select, cột khai báo kèm toán tử như sau, tên cột và toán tử cách nhau dấu :
```
$data = $this->account->select(['name', 'email', 'password_display'])  
			->where([
				'id' => 2,
				"email:=" => "dung@gmail.com",
                "deleted_at" => DBCRUD::IS_NULL,
				'created_at:>=' => '2022-01-02'
			]) | whereOr
			->order('id') | order('id', 'DESC') | order('id,name,email', 'DESC')
			->get(index) | first() | last(); 
```
_Câu truy vấn tương ứng:_
```
SELECT name,email,password_display FROM account WHERE id = 1 AND email = 'abc@gmail.com' AND deleted_at IS NULL AND created_at >= '2022-01-02' ORDER BY id ASC
```

#### Sử dụng truy vấn select whereLike
```
$data = $this->account->select(['name', 'email', 'password_display']) 
			->whereLike([
				"email" => "dung@gmail.com",
                "name" => "dung",
			])
			->order('id')
			->get();
hoặc
$data = $this->account->select(['name', 'email', 'password_display']) 
			->where([
				"email:like" => "dung@gmail.com",
                "name:like" => "dung",
			])
			->order('id')
			->get();
```
_Câu truy vấn tương ứng:_
```
SELECT name,email,password_display FROM account WHERE email LIKE '%dung@gmail.com%' AND name LIKE '%dung%' ORDER BY id ASC
```

#### Sử dụng truy vấn select whereLikeOr
```
$data = $this->account->select(['name', 'email', 'password_display']) 
			->whereLikeOr([
				"email" => "dung@gmail.com",
                "name" => "dung",
			])
			->order('id')
			->get();
hoặc
$data = $this->account->select(['name', 'email', 'password_display']) 
			->whereOr([
				"email:like" => "dung@gmail.com",
                "name:like" => "dung",
			])
			->order('id')
			->get();
```
_Câu truy vấn tương ứng:_
```
SELECT name,email,password_display FROM account WHERE email LIKE '%dung@gmail.com%' OR name LIKE '%dung%' ORDER BY id ASC
```

#### Truy vấn join, có thể đặt tên trong select 'roles.name as role_name'

```
$data = $this->account->select([
				'account.id', 
				'account.is_super', 
				'account.name', 
				'account.email', 
				'account.password_display', 
				'account_role.role_id',
				'roles.name as role_name'
			])
			->join('account_role', function($join) {
				$join->on('account.id', '=', 'account_role.account_id');
			})
			->join('roles', function($join) {
				$join->on('account_role.role_id', '=', 'roles.id');
			})
		->order('account.id')
		->get();
```
_Câu truy vấn tương ứng:_
```
SELECT account.id,account.is_super,account.name,account.email,account.password_display,account_role.role_id,roles.name as role_name FROM account 
INNER JOIN  account_role ON account.id = account_role.account_id 
INNER JOIN  roles ON account_role.role_id = roles.id 
ORDER BY account.id ASC
```
#### Truy vấn leftJoin của bảng kết quả và where trên kết quả join
```
$data = $this->account->select([
				'account.id', 
				'account.is_super', 
				'account.name', 
				'account.email', 
				'account.password_display', 
				'account_role.role_id',
				'roles.name as role_name'
			])
			->leftJoin('account_role', function($join) {
				$join->on('account.id', '=', 'account_role.account_id');
			})
			->leftJoin('roles', function($join) {
				$join->on('account_role.role_id', '=', 'roles.id');
			})
			->where([
				'account.is_super' => 0
			])
		->order('account.id')
		->get();
```
_Câu truy vấn tương ứng:_
```
SELECT account.id,account.is_super,account.name,account.email,account.password_display,account_role.role_id,roles.name as role_name FROM account 
LEFT JOIN  account_role ON account.id = account_role.account_id
LEFT JOIN  roles ON account_role.role_id = roles.id 
WHERE account.is_super = 0
ORDER BY account.id ASC
```

#### Truy vấn kết hợp join
```
$data = $this->account->select([
				'account.id', 
				'account.is_super', 
				'account.name', 
				'account.email', 
				'account.password_display', 
				'account_role.role_id',
				'roles.name as role_name'
			])
			->leftJoin('account_role', function($join) {
				$join->on('account.id', '=', 'account_role.account_id');
			})
			->join('roles', function($join) {
				$join->on('account_role.role_id', '=', 'roles.id');
			})
			->where([
				'account.is_super' => 0
			])
		->order('account.id')
		->get();
```
_Câu truy vấn tương ứng:_
```
SELECT account.id,account.is_super,account.name,account.email,account.password_display,account_role.role_id,roles.name as role_name FROM account 
LEFT JOIN  account_role ON account.id = account_role.account_id 
INNER JOIN  roles ON account_role.role_id = roles.id 
WHERE account.is_super = 0 
ORDER BY account.id ASC
```
#### Truy vấn kết hợp rightJoin
```
$data = $this->account->select([
				'account.id', 
				'account.is_super', 
				'account.name', 
				'account.email', 
				'account.password_display', 
				'account_role.role_id',
				'roles.name as role_name'
			])
			->rightJoin('account_role', function($join) {
				$join->on('account.id', '=', 'account_role.account_id');
			})
			->rightJoin('roles', function($join) {
				$join->on('account_role.role_id', '=', 'roles.id');
			})
		->order('account.id')
		->get();
```
_Câu truy vấn tương ứng:_
```
SELECT account.id,account.is_super,account.name,account.email,account.password_display,account_role.role_id,roles.name as role_name FROM account RIGHT JOIN  account_role ON account.id = account_role.account_id RIGHT JOIN  roles ON account_role.role_id = roles.id ORDER BY account.id ASC
```

#### Update dữ liệu với điều kiện where AND
```
$data = $this->account->update([
			'name' => 'zzzz',
			"email" => "zzzz@gmail.com",
		], [
			"email" => "abc@gmail.com",
			"name" => "abc",
		]);

hoặc với điều kiện so sánh

$data = $this->account->update([
			'name' => 'zzzz',
			"email" => "zzzz@gmail.com",
		], [
			"id:=" => 2,
			"name:like" => "abc",
		]);
```
_Câu truy vấn tương ứng:_
```
UPDATE account SET name = 'zzzz', email = 'zzzz@gmail.com' WHERE email = '%abc@gmail.com%' AND name = 'abc'
UPDATE account SET name = 'zzzz', email = 'zzzz@gmail.com' WHERE id = 2 AND name LIKE '%abc%'
```
#### Update dữ liệu với điều kiện where OR
```
$data = $this->account->updateOr([
							'name' => 'abc'
						], [
							'id' => 2,
							'password_display' => '123123'
						]);

hoặc với điều kiện sao sánh

$data = $this->account->updateOr([
					'name' => 'abc'
				], [
					'id:>=' => 2,
					'password_display:like' => '123123'
				]);
```
_Câu truy vấn tương ứng:_
```
UPDATE account SET name = 'abc' WHERE id = 2 OR password_display = '123123'
UPDATE account SET name = 'abc' WHERE id >= 2 OR password_display LIKE '%123123%'
```
#### Tạo một dòng trong db
```
$data = $this->account->create([
			'name' => 'asd',
			"email" => "acs@gmail.com",
			'password_display' => '12312312',
			'password' => '12312312',
			'is_super' => 2
		]);
```
_Câu truy vấn tương ứng:_
```
INSERT INTO account (id,name,email,email_verified_at,password_display,password,is_super,remember_token,created_at,updated_at,deleted_at) VALUES (null,'asd','acs@gmail.com',null,'12312312','12312312',2,null,'2023-04-03 22:39:40','2023-04-03 22:39:40',null)
```
#### Tạo một n dòng trong db
```
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
```

#### Tìm một dòng với điều kiện, nếu không có trong db thì sẽ tạo mới
```
$data = $this->account->findOrCreate([
			'name' => 'ads',
			"email" => "ads@gmail.com",
			'password_display' => '12312312',
			'password' => '12312312',
			'is_super' => 2
		]);

```
#### Xoá một dòng trong db với điều kiện
```
$data = $this->account->destroy([
			'name' => 'ads',
			"email" => "ads@gmail.com",
			'password_display' => '12312312',
			'password' => '12312312',
			'is_super' => 2
		]);
```
_Câu truy vấn tương ứng:_
```
DELETE FROM account WHERE name = 'ads' AND email = 'ads@gmail.com' AND password_display = '12312312' AND password = '12312312' AND is_super = 2
```

#### Xoá logic một dòng trong db với điều kiện
```
$data = $this->account->destroySoft([
			'name' => 'ads',
			"email" => "ads@gmail.com",
			'password_display' => '12312312',
			'password' => '12312312',
			'is_super' => 2
		]);
```
_Câu truy vấn tương ứng:_
```
UPDATE account SET deleted_at = '2023-04-03 22:58:06' WHERE name = 'ads' AND email = 'ads@gmail.com' AND password_display = '12312312' AND password = '12312312' AND is_super = 2
```

## License

[MIT](https://choosealicense.com/licenses/mit/)