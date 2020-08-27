{{-- layouts/admin.blade.phpを読み込む --}}
@extends('layouts.admin')


{{-- admin.blade.phpの@yield('title')に'ファミリー情報'を埋め込む --}}
@section('title', 'ファミリー情報入力')

{{-- admin.blade.phpの@yield('content')に以下のタグを埋め込む --}}
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <h2>ファミリー情報入力</h2>
                <form action="{{ action('Admin\FamilyController@create') }}" method="post" enctype="multipart/form-data">

                    @if (count($errors) > 0)
                        <ul>
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="form-group row">
                        <label class="col-md-2">ファミリー名</label>
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="fname" value="{{ old('fname') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2">配偶者の有無</label>
                        <div class="col-md-10">
                            <input type="checkbox" name="marital_status" value="1" checked="checked">有り
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2">子の数</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control" name="n_child" value="{{ old('n_child') }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2">備考</label>
                        <div class="col-md-10">
                            <textarea class="form-control" name="exinfo" rows="5">{{ old('exinfo') }}</textarea>
                        </div>
                    </div>
                    {{ csrf_field() }}
                    <input type="submit" class="btn btn-primary" value="更新">
                </form>

            </div>
        </div>
    </div>
@endsection
