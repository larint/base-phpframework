@extend layout

@main_content
<div class="mt-5">
    Read data from db
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $item):?>
            <tr>
                <td><?= $item->name ?></td>
                <td><?= $item->email ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
@end_main_content