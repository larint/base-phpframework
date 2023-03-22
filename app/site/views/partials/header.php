<div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom box-shadow">
    <h5 class="my-0 mr-md-auto font-weight-normal"><a href="<?= home_url() ?>">Home Page</a></h5>
    <nav class="my-2 my-md-0 mr-md-3">
        <a class="p-2 text-dark" href="<?= route('pageQuery', [123, 'dung']) ?>">Query Url</a>
        <a class="p-2 text-dark" href="<?= route('form') ?>">Form Example</a>
    </nav>
    <a class="btn btn-outline-primary" href="#">Sign up</a>
</div>