<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Family;

class FamilyController extends Controller
{
  public function add()
  {
      return view('admin.family.create');
  }
  
  public function create(Request $request)
  {
      // Varidationを行う
      $this->validate($request, Family::$rules);
      $family = new Family;
      $form = $request->all();
      // フォームから送信されてきた_tokenを削除する
      unset($form['_token']);
      // データベースに保存する
      $family->fill($form);
      $family->save();
      
      return redirect('admin/family/create');
  }

  public function index(Request $request)
  {
      $cond_title = $request->cond_title;
      if ($cond_title != '') {
          // 検索されたら検索結果を取得する
          $posts = Family::where('fname', $cond_title)->get();
      } else {
          // それ以外はすべてのニュースを取得する
          $posts = Family::all();
      }
      return view('admin.family.index', ['posts' => $posts, 'cond_title' => $cond_title]);
  }

  public function edit()
  {
      return view('admin.family.edit');
  }

  public function update()
  {
      return redirect('admin/family/edit');
  }
  
}
