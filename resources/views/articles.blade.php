@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="excel float-sm-left mb-2">
            <a class="btn btn-sm btn-primary float-sm-left" href="{{ route('export.articles') }}">Download excel</a>
        </div>
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Description</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($articles as $article)
                            <tr>
                                <td><?php echo $article->title; ?></td>
                                <td>
                                    <?php echo limit_text($article->description, 40); ?>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
