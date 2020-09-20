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
  /*
  public function add2()  //顧客一覧へ戻るボタン対応
  {
      return redirect('admin/family');
  }
  */
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
      
      return redirect('admin/rate/create')->with('idid',$family->id);  //return viewだとURLが変わらないので、redirectを使用
      //return redirect('admin/family/create');
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

  public function edit(Request $request)
  {
      // News Modelからデータを取得する
      $family = Family::find($request->id);
      if (empty($family)) {
        abort(404);    
      }
      return view('admin.family.edit', ['family_form' => $family]);
  }
  
 

  public function update(Request $request)
  {
      // Validationをかける
      $this->validate($request, Family::$rules);
      // News Modelからデータを取得する
      $family = Family::find($request->id);
      // 送信されてきたフォームデータを格納する
      $family_form = $request->all();
      unset($family_form['_token']);

      // 該当するデータを上書きして保存する
      $family->fill($family_form)->save();

      return redirect('admin/rate/create')->with('idid',$request->id);  //return viewだとURLが変わらないので、redirectを使用
  }
  
  public function delete(Request $request)
  {
      // 該当するNews Modelを取得
      $family = Family::find($request->id);
      // 削除する
      $family->delete();
      return redirect('admin/family/');
  }
  
}
