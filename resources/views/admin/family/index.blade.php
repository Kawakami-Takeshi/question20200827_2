@extends('layouts.admin')
@section('title', '登録済みファミリーの一覧')

@section('content')
    <div class="container">
        <div class="row">
            <h2>ファミリー一覧</h2>
        </div>
        <div class="row">
            <div class="col-md-4">
                <a href="{{ action('Admin\FamilyController@add') }}" role="button" class="btn btn-primary">新規作成</a>
            </div>
            <div class="col-md-8">
                <form action="{{ action('Admin\FamilyController@index') }}" method="get">
                    <div class="form-group row">
                        <label class="col-md-2">ファミリー名</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="cond_title" value="{{ $cond_title }}">
                        </div>
                        <div class="col-md-2">
                            {{ csrf_field() }}
                            <input type="submit" class="btn btn-primary" value="検索">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="list-family col-md-12 mx-auto">
                <div class="row">
                    <table class="table table-dark">
                        <thead>
                            <tr>
                                <th width="10%">ID</th>
                                <th width="20%">ファミリー名</th>
                                <th width="10%">配偶者有無</th>
                                <th width="10%">子供の数</th>
                                <th width="30%">備考</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posts as $family)
                                <tr>
                                    <th>{{ $family->id }}</th>
                                    <td>{{ \Str::limit($family->fname, 100) }}</td>
                                    <td>{{ \Str::limit($family->marital_status, 50) }}</td>
                                    <td>{{ \Str::limit($family->n_child, 50) }}</td>
                                    <td>{{ \Str::limit($family->exinfo, 100) }}</td>
                                    <td>
                                        <div>
                                            <a href="{{ action('Admin\FamilyController@edit', ['id' => $family->id]) }}">編集</a>
                                        </div>
                                        <div>
                                            <a href="{{ action('Admin\FamilyController@delete', ['id' => $family->id]) }}">削除</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection