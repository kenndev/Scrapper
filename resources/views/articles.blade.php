@extends('layouts.app')

@section('content')
    <div class="container">
        
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
                        @foreach ($duplicates as $article)
                            <tr>
                                <td><?php echo $article->title; ?></td>
                                <td>
                                    <?php echo $article->url_id; ?>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
