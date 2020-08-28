<?php
  // 画面から送られたきた値
  $id = filter_input(INPUT_POST, 'id');	// $_POST['id']とも書ける
  $list = array("id" => $id, "name" => "お名前", "hoge" => "ほげ" );
  // 明示的に指定しない場合は、text/html型と判断される
  header("Content-type: application/json; charset=UTF-8");
  //JSONデータを出力
  echo json_encode($list);
  exit;