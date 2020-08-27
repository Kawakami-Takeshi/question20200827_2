<?php
//相続人ID,相続税法定相続割合分子,相続税法定相続割合分母,民法法定相続割合分子,民法法定相続割合分母,配偶者フラグ,二割加算フラグ,
//相続人IDを1次元目、法定相続割合や財産など14項目を２次元目とした多重連想配列をインプットとする
$d1=array(1,2,3);  //相続人ID
$d2='相続人名';
$d3='相続税法定相続割合分子';
$d4='相続税法定相続割合分母';
$d5='民法法定相続割合分子';
$d6='民法法定相続割合分母';
$d7='配偶者フラグ';
$d8='二割加算フラグ';
$d9='本来の取得財産';   //死亡保険金、退職金、相続時精算課税、3年内贈与財産除く
$d10='死亡保険金';
$d11='死亡退職金';
$d12='相続時精算課税贈与財産';
$d13='三年内贈与財産';
$d14='贈与税額控除';
$d15='相続時精算課税に係る贈与税';

$list = array(
  $d1[0] => array(
      $d2 => '配偶者', 
      $d3 => 1, 
      $d4 => 2,
      $d5 => 1,
      $d6 => 2, 
      $d7 => 1, 
      $d8 => 0,
      $d9 => 10000,
      $d10 => 1650,
      $d11 => 3000,
      $d12 => 0,
      $d13 => 0,
      $d14 => 0,
      $d15 => 0,
  )
);
  
for($i=1 ; $i< count($d1) ; $i++){
  $list = $list+array(
      $d1[$i] => array(
                        $d2 => '子', 
                        $d3 => 1, 
                        $d4 => 4,
                        $d5 => 1,
                        $d6 => 4, 
                        $d7 => 0, 
                        $d8 => 0,
                        $d9 => 12000,
                        $d10 => 600,
                        $d11 => 0,
                        $d12 => 0,
                        $d13 => 0,
                        $d14 => 0,
                        $d15 => 0,
      )
  );
}

function i_tax($list){
  //INPUT
  //相続人の名前を1次元目、下記14項目を２次元目とした多重連想配列
  //相続人ID,相続税法定相続割合分子,相続税法定相続割合分母,民法法定相続割合分子,民法法定相続割合分母,配偶者フラグ,二割加算フラグ
  //本来の取得財産(死亡保険金、退職金、相続時精算課税、3年内贈与財産除く),死亡保険金,死亡退職金
  //相続時精算課税贈与財産,三年内贈与財産,贈与税額控除,相続時精算課税に係る贈与税
  
  //OUTPUT・・・INPUTに下記項目を追加した配列(計33項目)
  //保険非課税計算対象額,生命保険非課税額,退職金非課税計算対象額,退職金非課税額,純資産価額,課税価格
  //課税遺産額,仮税額,按分税額,二割加算額,仮納付税額,配偶者の法定相続分,配偶者の課税価格,配偶者の税額軽減
  //未成年者控除,障害者控除,相似相続控除,外国税額控除,納付税額
  
  //インプット・アウトプットともに金額は全て万円単位
  
  $bunsi = array_column($list,'相続税法定相続割合分子');  
  $n1 = count($bunsi); //相続人の数
  $n2 = count(array_filter($bunsi,function($x){return $x>=1;})); //基礎控除カウント用の相続人の数(分子が1以上の人数をカウント)
  $kiso = 3000 + 600 * $n2 ;  //基礎控除の額
  $hokenm=min(array_sum(array_column($list,'死亡保険金')),$n2*500); //死亡保険金の非課税額合計値
  $taim=min(array_sum(array_column($list,'死亡退職金')),$n2*500); //死亡退職金の非課税額合計値
  //死亡保険金の非課税額
  for($i=1 ; $i<= $n1 ; $i++){
    if($list[$i]['民法法定相続割合分子']>0){
      $thoken= $list[$i]['死亡保険金'];
    }else{
      $thoken=0;
    };
    $list[$i]['保険非課税計算対象額']=$thoken;  //配列
  };
  $thokeng=array_sum(array_column($list,'保険非課税計算対象額')); //非課税対象額の分母(合計値)
  for($i=1 ; $i<= $n1 ; $i++){
    if($list[$i]['民法法定相続割合分子']>0){
      $thoken= ($list[$i]['保険非課税計算対象額']/$thokeng)*$hokenm;
    }else{
      $thoken=0;
    };
    $list[$i]['生命保険非課税額']=floor( $thoken * pow( 10 , 4 ) ) / pow( 10 , 4 ) ;  //小数点第４位未満切り捨て
  };
  //死亡退職金の非課税額
  for($i=1 ; $i<= $n1 ; $i++){
    if($list[$i]['民法法定相続割合分子']>0){
      $thoken= $list[$i]['死亡退職金'];
    }else{
      $thoken=0;
    };
    $list[$i]['退職金非課税計算対象額']=$thoken;  //配列
  };
  $thokeng=array_sum(array_column($list,'退職金非課税計算対象額')); //非課税対象額の分母(合計値)
  for($i=1 ; $i<= $n1 ; $i++){
    if($list[$i]['民法法定相続割合分子']>0){
      $thoken= ($list[$i]['退職金非課税計算対象額']/$thokeng)*$taim;
    }else{
      $thoken=0;
    };
    $list[$i]['退職金非課税額']=floor( $thoken * pow( 10 , 4 ) ) / pow( 10 , 4 ) ;  //小数点第４位未満切り捨て
  };
  //純資産価額など計算
  for($i=1 ; $i<= $n1 ; $i++){
    $list[$i]['純資産価額']=$list[$i]['本来の取得財産']+$list[$i]['死亡保険金']-$list[$i]['生命保険非課税額']+$list[$i]['死亡退職金']-$list[$i]['退職金非課税額']+$list[$i]['相続時精算課税贈与財産'];
    $list[$i]['純資産価額']=max(0,$list[$i]['純資産価額']);
    //課税価格
    if($list[$i]['民法法定相続割合分子']>0 || ($list[$i]['本来の取得財産']>0 || ($list[$i]['死亡保険金']-$list[$i]['生命保険非課税額'])>0 || ($list[$i]['死亡退職金']-$list[$i]['退職金非課税額'])>0 || $list[$i]['相続時精算課税贈与財産']>0)){
      $list[$i]['課税価格']=$list[$i]['純資産価額']+$list[$i]['三年内贈与財産'];
    };
  };
  
  $ktotal=max(0,array_sum(array_column($list,'課税価格'))); //課税価格の合計値
  $kitotal=max(0,$ktotal-$kiso); //課税遺産総額（課税価格合計-基礎控除）
  
  //法定相続割合に基づく税額計算
  for($i=1 ; $i<= $n1 ; $i++){
    if($list[$i]['相続税法定相続割合分子']>0 && $kitotal>0){
      $kisan=$kitotal*($list[$i]['相続税法定相続割合分子']/$list[$i]['相続税法定相続割合分母']);
      $list[$i]['課税遺産額']=floor( $kisan * pow( 10 , 1 ) ) / pow( 10 , 1 );  //千円未満切り捨て
      //税額計算
      if($list[$i]['課税遺産額']<=1000){
        $zei=$list[$i]['課税遺産額']*0.1;
      }elseif($list[$i]['課税遺産額']>1000 && $list[$i]['課税遺産額']<=3000){
        $zei=$list[$i]['課税遺産額']*0.15-50;
      }elseif($list[$i]['課税遺産額']>3000 && $list[$i]['課税遺産額']<=5000){
        $zei=$list[$i]['課税遺産額']*0.2-200;
      }elseif($list[$i]['課税遺産額']>5000 && $list[$i]['課税遺産額']<=10000){
        $zei=$list[$i]['課税遺産額']*0.3-700;
      }elseif($list[$i]['課税遺産額']>10000 && $list[$i]['課税遺産額']<=20000){
        $zei=$list[$i]['課税遺産額']*0.4-1700;
      }elseif($list[$i]['課税遺産額']>20000 && $list[$i]['課税遺産額']<=30000){
        $zei=$list[$i]['課税遺産額']*0.45-2700;
      }elseif($list[$i]['課税遺産額']>30000 && $list[$i]['課税遺産額']<=60000){
        $zei=$list[$i]['課税遺産額']*0.5-4200;
      }elseif($list[$i]['課税遺産額']>60000){
        $zei=$list[$i]['課税遺産額']*0.55-7200;
      }
      $list[$i]['仮税額']=floor( $zei * pow( 10 , 4 ) ) / pow( 10 , 4 );  //円未満切り捨て
    }else{
      $list[$i]['課税遺産額']=0;
      $list[$i]['仮税額']=0;
    };
  };
  
  $ztotal=max(0,array_sum(array_column($list,'仮税額'))); //仮税額の合計値
  
  //納付税額など計算
  for($i=1 ; $i<= $n1 ; $i++){
    if($ztotal>0 && $list[$i]['課税価格']>0){
      $az=$ztotal*($list[$i]['課税価格']/$ktotal);
      $list[$i]['按分税額']=floor( $az * pow( 10 , 4 ) ) / pow( 10 , 4 );
      //二割加算
      if($list[$i]['二割加算フラグ']>0){
        $k2=$list[$i]['按分税額']*0.2;
        $list[$i]['二割加算額']=floor( $k2 * pow( 10 , 4 ) ) / pow( 10 , 4 );
      }else{
        $list[$i]['二割加算額']=0;
      };
      $list[$i]['仮納付税額']=$list[$i]['按分税額']+$list[$i]['二割加算額']-$list[$i]['贈与税額控除'];
      //配偶者の税額軽減
      if($list[$i]['配偶者フラグ']==1){
        $kh=$ktotal*($list[$i]['民法法定相続割合分子']/$list[$i]['民法法定相続割合分母']);
        $list[$i]['配偶者の法定相続分']=max(16000,floor( $kh * pow( 10 , 1 ) ) / pow( 10 , 1 ));
        $list[$i]['配偶者の課税価格']=max($list[$i]['課税価格'],$list[$i]['三年内贈与財産']);
        $mh=$ztotal*(min($list[$i]['配偶者の法定相続分'],$list[$i]['配偶者の課税価格'])/$ktotal);
        $mh=floor( $mh * pow( 10 , 4 ) ) / pow( 10 , 4 );
        $list[$i]['配偶者の税額軽減']=min($mh,$list[$i]['仮納付税額']);
      }else{
        $list[$i]['配偶者の法定相続分']=0;
        $list[$i]['配偶者の課税価格']=0;
        $list[$i]['配偶者の税額軽減']=0;
      };
      
    }else{
      $list[$i]['按分税額']=0;
      $list[$i]['二割加算額']=0;
      $list[$i]['仮納付税額']=0;
      $list[$i]['配偶者の法定相続分']=0;
      $list[$i]['配偶者の課税価格']=0;
      $list[$i]['配偶者の税額軽減']=0;
    };
    
    //未成年者控除など
    $list[$i]['未成年者控除']=0;
    $list[$i]['障害者控除']=0;
    $list[$i]['相似相続控除']=0;
    $list[$i]['外国税額控除']=0;
    
    //最終的に納付する額
    $nz=$list[$i]['仮納付税額']-($list[$i]['配偶者の税額軽減']+$list[$i]['未成年者控除']+$list[$i]['相似相続控除']+$list[$i]['外国税額控除']);
    $list[$i]['納付税額']=floor( $nz * pow( 10 , 2 ) ) / pow( 10 , 2 );
  };
  
  return($list);

};

print_r(i_tax($list));
//print_r($list);

?>