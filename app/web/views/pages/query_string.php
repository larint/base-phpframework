@extend layout

@main_content
<div class="mt-5">
    Example get query string
    <div class="form-group">
        <label for="exampleInputPassword1">ID: <?= $params->id ?></label>
        <br>
        <label for="exampleInputPassword1">Name: <?= $params->name ?></label>
    </div>
</div>
@end_main_content