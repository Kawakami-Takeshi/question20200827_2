<?PHP
header('Content-type: application/json; charset=utf-8');
$data1 = filter_input( INPUT_GET, 'fid' );  //ファミリーID  変数を受け取る場合
$data2 = filter_input( INPUT_GET, 'kkk' , FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);  //資産情報 配列を受け取る場合　DB格納用データ
$data2n=count($data2);  //資産のレコード数
$data3 = filter_input( INPUT_GET, 'finfo' , FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);  //ファミリー情報(（相続人ID,相続人,法定相続割合の分子,法定相続割合の分母) 配列を受け取る場合
$data3n=count($data3);  //ファミリー数
$datah = filter_input( INPUT_GET, 'hai' );  //配偶者有無  変数を受け取る場合

//MySQLへ入る呪文
$dbh = new PDO('mysql:host=127.0.0.1;dbname=inheritance;charset=utf8','kawakami','Kawa/202007');

//既存レコードを削除
$sqld = "DELETE FROM assets WHERE familyid = :id";
$stmtx = $dbh->prepare($sqld);
$params2 = array(':id'=>$data1);
$stmtx->execute($params2);
$stmtx = null;
$dbh = null;

//受け入れデータをassetsテーブルに格納
$dbh = new PDO('mysql:host=127.0.0.1;dbname=inheritance;charset=utf8','kawakami','Kawa/202007');
$sql = "INSERT INTO assets (familyid,familyname,iid,iname,category,assetname,hihokenid,ukeid,zoyoid,zoyoy,suryo,kingaku,zoyozei,created_at,updated_at)";
$sql.= " VALUES ( :familyid, :familyname, :iid, :iname, :category, :assetname, :hihokenid, :ukeid, :zoyoid, :zoyoy, :suryo, :kingaku, :zoyozei, now(), now())";
// 挿入する値は空のまま、SQL実行の準備をする
$stmt = $dbh->prepare($sql);
for($i=0 ; $i< $data2n ; $i++){
  // DBに挿入する値を配列に格納する
if($data2[$i][7]==""){
  $data2[$i][7]=null;
};
$params = array(':familyid' => $data2[$i][0],
                ':familyname' => $data2[$i][1],
                ':iid' => $data2[$i][2],
                ':iname' => $data2[$i][3],
                ':category' => $data2[$i][4],
                ':assetname' => null,
                ':hihokenid' => null,
                ':ukeid' => $data2[$i][7],
                ':zoyoid' => null,
                ':zoyoy' => null,
                ':suryo' => null,
                ':kingaku' => $data2[$i][11],
                ':zoyozei' => null,
                );
 
$stmt->execute($params);
};
$stmt = null;
$dbh = null;
//DBインプット完了


//相続税計算後の配列を再びcreate.blade.phpに返す

require_once '/home/ec2-user/environment/inheritance/myfunc/inheritance_tax.php';  //相続税計算関数

//関数へのインプット配列作成 START
$i=0;
//本来の取得資産集計
$honrai=0;
$hoken=0;
for($j=0 ; $j< $data2n ; $j++){
    if($data2[$j][2]==$data3[$i][0]){ //相続人IDが一致
      if(($data2[$j][4]=="金融資産")||($data2[$j][4]=="不動産")){
        $honrai = $honrai + $data2[$j][11];
      }elseif($data2[$j][4]=="死亡保険金"){
        $hoken = $hoken + $data2[$j][11];
      }
    }
};

$list = array(
  $data3[$i][0] => array(
      '相続人名' => $data3[$i][1], 
      '相続税法定相続割合分子' => $data3[$i][2], 
      '相続税法定相続割合分母' => $data3[$i][3],
      '民法法定相続割合分子' => $data3[$i][2],
      '民法法定相続割合分母' => $data3[$i][3], 
      '配偶者フラグ' => $datah*1, 
      '二割加算フラグ' => 0,
      '本来の取得財産' => $honrai*1,
      '死亡保険金' => $hoken*1,
      '死亡退職金' => 0,
      '相続時精算課税贈与財産' => 0,
      '三年内贈与財産' => 0,
      '贈与税額控除' => 0,
      '相続時精算課税に係る贈与税' => 0,
  )
);

//2人目以降
if($data3n>1){
  for($i=1 ; $i< count($data3) ; $i++){
    //本来の取得資産集計
    $honrai=0;
    $hoken=0;
    for($j=0 ; $j< $data2n ; $j++){
        if($data2[$j][2]==$data3[$i][0]){ //相続人IDが一致
          if(($data2[$j][4]=="金融資産")||($data2[$j][4]=="不動産")){
            $honrai = $honrai + $data2[$j][11];
          }elseif($data2[$j][4]=="死亡保険金"){
            $hoken = $hoken + $data2[$j][11];
          }
        }
    };
    
    $list = $list+array(
                   $data3[$i][0] => array(
                                   '相続人名' => $data3[$i][1], 
                                   '相続税法定相続割合分子' => $data3[$i][2], 
                                   '相続税法定相続割合分母' => $data3[$i][3],
                                   '民法法定相続割合分子' => $data3[$i][2],
                                   '民法法定相続割合分母' => $data3[$i][3], 
                                   '配偶者フラグ' => 0, 
                                   '二割加算フラグ' => 0,
                                   '本来の取得財産' => $honrai*1,
                                   '死亡保険金' => $hoken*1,
                                   '死亡退職金' => 0,
                                   '相続時精算課税贈与財産' => 0,
                                   '三年内贈与財産' => 0,
                                   '贈与税額控除' => 0,
                                   '相続時精算課税に係る贈与税' => 0,
                   )
    );
  };
};
//関数へのインプット配列作成 END

//相続税計算
$list2=i_tax($list);

//create.blade.phpに返す値の成型
$hzei=array_column($list2,'生命保険非課税額');
$nzeig=array_sum($hzei);
array_unshift($hzei, $nzeig);  //非課税額(1行n列(合計と各相続人分))

$kzei=array_column($list2,'課税価格');
$nzeig=array_sum($kzei);
array_unshift($kzei, $nzeig);  //課税遺産額

$nzei=array_column($list2,'納付税額');
$nzeig=array_sum($nzei);
array_unshift($nzei, $nzeig);  //納付税額

$zei=array(
     $hzei,
     $kzei,
     $nzei
  );


$param=$zei;
//$param=$list2[2]['相続人名'];
echo json_encode( $param ); //　JSON形式に変換してから返す

?>