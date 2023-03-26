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
## License

[MIT](https://choosealicense.com/licenses/mit/)