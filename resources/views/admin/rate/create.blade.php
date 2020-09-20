<?php
   //familiesテーブルからデータ取得
   $nnn=session('idid');
   ///$nnn=3;
   $fid=$nnn;//$family_form->id; //familiesテーブルのid
   $fupdate = \App\Family::whereIn('id', [$fid])->value('updated_at');  //ファミリー情報の更新日時
   $aupdate = \App\Asset::whereIn('familyid', [$fid])->value('updated_at');  //資産情報の更新日時
   $faname = \App\Family::whereIn('id', [$fid])->value('fname');  //ファミリー名
   $hai = \App\Family::whereIn('id', [$fid])->value('marital_status');  //配偶者有無
   if (is_null($hai)){
     $hai=0;
   }
   $kazu = \App\Family::whereIn('id', [$fid])->value('n_child');  //子供の数
   //関数で必要な情報を計算
   require_once '/home/ec2-user/environment/inheritance/myfunc/inheritance_rate.php';
   $aaa=i_rate($hai,$kazu);
   $bbb=count($aaa);  //配列の数
   $bbb_json=json_encode($bbb);  //相続人の数をJSに渡すために変換
   $aaa_json=json_encode($aaa);  //（相続人ID,相続人,法定相続割合の分母,法定相続割合の分子)の配列をJSに渡すために変換
   $fname_json=json_encode($faname);
   $fid_json=json_encode($fid); //ファミリーID
   $hai_json=json_encode($hai); //配偶者フラグ
   //ファミリーの資産情報を取得
   //MySQLへ入る呪文
   $dbh = new PDO('mysql:host=127.0.0.1;dbname=inheritance;charset=utf8','kawakami','Kawa/202007');
   $sql = "SELECT iid,category,kingaku FROM assets WHERE familyid = ".$fid;
   $stmt = $dbh->query($sql);
   $kg=0;
   $fg=0;
   $hg=0;
   foreach ($stmt as $row) {
       // データベースのフィールド名で出力
       if($row['category']=='金融資産'){
           ${'k'.$row['iid']}=$row['kingaku'];  //k+相続人IDの変数に金融資産の金額を代入
           ${'kj'.$row['iid']}=json_encode(${'k'.$row['iid']});  //jsで入れるために変換
           $kg=$kg+$row['kingaku'];
       }elseif($row['category']=='不動産'){
           ${'f'.$row['iid']}=$row['kingaku'];  //f+相続人IDの変数に不動産の金額を代入
           ${'fj'.$row['iid']}=json_encode(${'f'.$row['iid']});
           $fg=$fg+$row['kingaku'];
       }elseif($row['category']=='死亡保険金'){
           ${'h'.$row['iid']}=$row['kingaku'];  //h+相続人IDの変数に死亡保険金の金額を代入
           ${'hj'.$row['iid']}=json_encode(${'h'.$row['iid']});
           $hg=$hg+$row['kingaku'];
       };
       
   };
   //金融資産の配列を作成
   if($kg>0){
       for($i=1;$i<=$bbb;$i++){
           if(isset(${'k'.$i})==1){
               $kh[]=[$i,${'k'.$i}];
           }else{
               $kh[]=[$i,0];
           }
       }
   }else{
       $kh=0;
   }
   $kh=json_encode($kh);

   //不動産の配列を作成
   if($fg>0){
       for($i=1;$i<=$bbb;$i++){
           if(isset(${'f'.$i})==1){
               $fh[]=[$i,${'f'.$i}];
           }else{
               $fh[]=[$i,0];
           }
       }
   }else{
       $fh=0;
   }
   $fh=json_encode($fh);
   //保険の配列を作成
   if($hg>0){
       for($i=1;$i<=$bbb;$i++){
           if(isset(${'h'.$i})==1){
               $hh[]=[$i,${'h'.$i}];
           }else{
               $hh[]=[$i,0];
           }
       }
   }else{
       $hh=0;
   }
   $hh=json_encode($hh);
   
   //「資産の更新日時＜家族情報の更新日時」だったら、資産情報をリセットする
   if($aupdate<$fupdate){
       $kg=0;
       $fg=0;
       $hg=0;
   }
   $kg=json_encode($kg);
   $fg=json_encode($fg);
   $hg=json_encode($hg);
?>

{{-- layouts/admin.blade.phpを読み込む --}}
@extends('layouts.admin')


{{-- admin.blade.phpの@yield('title')に'ファミリー情報'を埋め込む --}}
@section('title', '資産情報入力')

{{-- admin.blade.phpの@yield('content')に以下のタグを埋め込む --}}
@section('content')


    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <h2>{{$faname}} 様の資産情報入力</h2>
                <p></p>
                <p>※1　数値のみ入力可能です。<br>
                ※2　合計資産額を入力すれば、各相続人への遺産配分額を入力することが出来ます。<br>
                ※3　各相続人への遺産配分額は法定相続割合が初期値となっています。</p>
                <p></p>
                <button id="btn" class="btn btn-primary">計算開始</button>
            
                    <a href="{{ action('Admin\FamilyController@index') }}" role="button" class="btn btn-secondary">一覧へ戻る</a>
                
                
                <p></p>
                <table id="myTBL">
                    <thread>
                            <tr>
                               <th width="180"></th>
                               <th>合計</th>
 
                            </tr>
                    </thread>
                    <tbody>
                        <tr>
                            <td width="180">法定相続割合</td>
                            <td>100 / 100</td>
                            
                        </tr>
                        <tr>
                            <td width="180">金融資産（万円）</td>
                            <td>
                                <input type="text" oninput="value = value.replace(/[^0-9]+/i,'');" id="abox1" class="form-control" value="" onchange="func1()">
                            </td>
                        </tr>
                        <tr>
                            <td width="180">不動産（万円）</td>
                            <td>
                                <input type="text" oninput="value = value.replace(/[^0-9]+/i,'');" id="abox2" class="form-control" value="" onchange="func2()">
                            </td>
                        </tr>
                        <tr>
                            <td width="180">死亡保険金（万円）</td>
                            <td>
                                <input type="text" oninput="value = value.replace(/[^0-9]+/i,'');" id="abox3" class="form-control" value="" onchange="func3()">
                            </td>
                        </tr>
                        <tr>
                            <td width="180">合計（万円）</td>
                            <td>
                                <input type="text" style="background-color:#000000;font-size:15;color:#FFFFFF;border:none" id="totallall" class="form-control" value=0 disabled=true>
                            </td>
                        </tr>
                        <tr>
                            <!--保険金の非課税額-->
                            <td>
                                <input type="text" width="180" style="background-color:#000000;font-size:15;color:#FFFFFF;border:none" id="hht" class="form-control" value="" disabled=true>
                            </td>
                            <td>
                                <input type="text" style="background-color:#000000;font-size:15;color:#FFFFFF;border:none" id="hhall" class="form-control" value="" disabled=true>
                            </td>
                        </tr>
                        <tr>
                            <!--課税遺産総額-->
                            <td>
                                <input type="text" width="180" style="background-color:#000000;font-size:15;color:#FFFFFF;border:none" id="kit" class="form-control" value="" disabled=true>
                            </td>
                            <td>
                                <input type="text" style="background-color:#000000;font-size:15;color:#FFFFFF;border:none" id="kiall" class="form-control" value="" disabled=true>
                            </td>
                        </tr>
                        <tr>
                            <!--納税額-->
                            <td>
                                <input type="text" width="180" style="background-color:#000000;font-size:15;color:#FFFFFF;border:none" id="nzt" class="form-control" value="" disabled=true>
                            </td>
                            <td>
                                <input type="text" style="background-color:#000000;font-size:15;color:#FFFFFF;border:none" id="nzall" class="form-control" value="" disabled=true>
                            </td>
                        </tr>
                        <tr>
                            <!--正味承継資産-->
                            <td>
                                <input type="text" width="180" style="background-color:#000000;font-size:15;color:#FFFFFF;border:none" id="sst" class="form-control" value="" disabled=true>
                            </td>
                            <td>
                                <input type="text" style="background-color:#000000;font-size:15;color:#FFFFFF;border:none" id="ssall" class="form-control" value="" disabled=true>
                            </td>
                        </tr>
                    </tbody>
                               <script type="text/javascript">
                                 var c_end = '<?php echo $bbb_json; ?>'  //変数をphpから持ってくる場合
                                 var ary = JSON.parse('<?php echo addslashes($aaa_json) ; ?>'); //配列をphpから持ってくる場合
                                 
                                 //既にDBに値があるかどうかの判定をするための変数呼び出し
                                 var kinyu=JSON.parse('<?php echo ($kg) ; ?>');  //金融資産の既存データ有無 0:なし,0より大きければ有り
                                 if(kinyu>0){
                                     var kh = JSON.parse('<?php echo addslashes($kh) ; ?>'); //配列をphpから持ってくる場合
                                     document.getElementById("abox1").setAttribute("value", kinyu);
                                 }
                                 var fudosan=JSON.parse('<?php echo ($fg) ; ?>');  //不動産の既存データ有無 0:なし,0より大きければ有り
                                 if(fudosan>0){
                                     var fh = JSON.parse('<?php echo addslashes($fh) ; ?>'); //配列をphpから持ってくる場合
                                     document.getElementById("abox2").setAttribute("value", fudosan);
                                 }
                                 var hoken=JSON.parse('<?php echo ($hg) ; ?>');  //保険の既存データ有無 0:なし,0より大きければ有り
                                 if(hoken>0){
                                     var hh = JSON.parse('<?php echo addslashes($hh) ; ?>'); //配列をphpから持ってくる場合
                                     document.getElementById("abox3").setAttribute("value", hoken);
                                 }
                                 if(kinyu*1+fudosan*1+hoken*1>0){
                                     document.getElementById("totallall").setAttribute("value", kinyu*1+fudosan*1+hoken*1);
                                 }
                                 
                                 var tblObj=document.getElementById("myTBL");
                                 for(var i=2;i<=c_end+1;i++){  //ヘッダーのみ列追加
                                   //相続人名
                                   var th = document.createElement( 'th' ); // <TH>要素を生成
                                   tblObj.rows[0].appendChild( th ); // 一行目に<TH>を付加
                                   tblObj.rows[0].cells[i].innerHTML= ary[i-2][1]; // セル内容
                                   
                                   //法定相続割合
                                   tblObj.rows[1].insertCell(-1);
                                   var td = document.createElement( 'td' ); // <TD>要素を生成
                                   tblObj.rows[1].cells[i].innerHTML= ary[i-2][2]+" / "+ary[i-2][3]; // 法定相続割合
                                   //デフォルトは入力不可のテキストボックス（各人の相続額）
                                   tblObj.rows[2].insertCell(-1);// インプット用のセルを追加（金融資産）
                                   var input1 = document.createElement("input");
                                   input1.setAttribute("type","text"); 
		                           input1.setAttribute("class","form-control");
		                           input1.setAttribute("id","mbox"+ary[i-2][0]);
		                           if(kinyu>0){
		                               input1.setAttribute("value",kh[i-2][1]);
		                               //input1.setAttribute("disabled",false);
		                           }else{
		                               input1.setAttribute("value","");
		                               input1.setAttribute("disabled",true);
		                           }
		                           input1.setAttribute("onchange", "func10()");
		                           tblObj.rows[2].cells[i].appendChild(input1);
		                           tblObj.rows[3].insertCell(-1);// インプット用のセルを追加（不動産）
                                   var input1 = document.createElement("input");
                                   input1.setAttribute("type","text"); 
		                           input1.setAttribute("class","form-control");
		                           input1.setAttribute("id","rbox"+ary[i-2][0]);
		                           if(fudosan>0){
		                               input1.setAttribute("value",fh[i-2][1]);
		                               //input1.setAttribute("disabled",false);
		                           }else{
		                               input1.setAttribute("value","");
		                               input1.setAttribute("disabled",true);
		                           }
		                           input1.setAttribute("onchange", "func10()");
		                           tblObj.rows[3].cells[i].appendChild(input1);
		                           tblObj.rows[4].insertCell(-1);// インプット用のセルを追加（死亡保険金）
                                   var input1 = document.createElement("input");
                                   input1.setAttribute("type","text"); 
		                           input1.setAttribute("class","form-control");
		                           input1.setAttribute("id","ibox"+ary[i-2][0]);
		                           if(hoken>0){
		                               input1.setAttribute("value",hh[i-2][1]);
		                               //input1.setAttribute("disabled",false);
		                           }else{
		                               input1.setAttribute("value","");
		                               input1.setAttribute("disabled",true);
		                           }
		                           input1.setAttribute("onchange", "func10()");
		                           tblObj.rows[4].cells[i].appendChild(input1);
		                           //合計値,非入力セル
                                   tblObj.rows[5].insertCell(-1);
                                   var input1 = document.createElement("input");
                                   input1.setAttribute("type","text"); 
		                           input1.setAttribute("class","form-control");
		                           input1.setAttribute("id","tbox"+ary[i-2][0]);
		                           if(kinyu+fudosan+hoken>0){
		                               var gokei=0;
		                               if(kinyu>0){ gokei=gokei*1+kh[i-2][1]*1; }
		                               if(fudosan>0){ gokei=gokei*1+fh[i-2][1]*1; }
		                               if(hoken>0){ gokei=gokei*1+hh[i-2][1]*1; }
		                               input1.setAttribute("value",gokei);
		                           }else{
		                               input1.setAttribute("value",0);
		                           }
		                           input1.setAttribute("style","background-color:#000000;font-size:15;color:#FFFFFF;border:none");
		                           input1.setAttribute("disabled",true);
		                           tblObj.rows[5].cells[i].appendChild(input1);
		                           //保険非課税額,非入力セル
                                   tblObj.rows[6].insertCell(-1);
                                   var input1 = document.createElement("input");
                                   input1.setAttribute("type","text"); 
		                           input1.setAttribute("class","form-control");
		                           input1.setAttribute("id","hhbox"+ary[i-2][0]);
		                           input1.setAttribute("value","");
		                           input1.setAttribute("style","background-color:#000000;font-size:15;color:#FFFFFF;border:none");
		                           input1.setAttribute("disabled",true);
		                           tblObj.rows[6].cells[i].appendChild(input1);
		                           //課税遺産総額,非入力セル
                                   tblObj.rows[7].insertCell(-1);
                                   var input1 = document.createElement("input");
                                   input1.setAttribute("type","text"); 
		                           input1.setAttribute("class","form-control");
		                           input1.setAttribute("id","kibox"+ary[i-2][0]);
		                           input1.setAttribute("value","");
		                           input1.setAttribute("style","background-color:#000000;font-size:15;color:#FFFFFF;border:none");
		                           input1.setAttribute("disabled",true);
		                           tblObj.rows[7].cells[i].appendChild(input1);
		                           //納税額,非入力セル
                                   tblObj.rows[8].insertCell(-1);
                                   var input1 = document.createElement("input");
                                   input1.setAttribute("type","text"); 
		                           input1.setAttribute("class","form-control");
		                           input1.setAttribute("id","nzbox"+ary[i-2][0]);
		                           input1.setAttribute("value","");
		                           input1.setAttribute("style","background-color:#000000;font-size:15;color:#FFFFFF;border:none");
		                           input1.setAttribute("disabled",true);
		                           tblObj.rows[8].cells[i].appendChild(input1);
		                           //正味承継資産,非入力セル
                                   tblObj.rows[9].insertCell(-1);
                                   var input1 = document.createElement("input");
                                   input1.setAttribute("type","text"); 
		                           input1.setAttribute("class","form-control");
		                           input1.setAttribute("id","ssbox"+ary[i-2][0]);
		                           input1.setAttribute("value","");
		                           input1.setAttribute("style","background-color:#000000;font-size:15;color:#FFFFFF;border:none");
		                           input1.setAttribute("disabled",true);
		                           tblObj.rows[9].cells[i].appendChild(input1);
                                 }
                              
                               </script>
                </table>
                
                <p></p>
                
 
                
                <script type="text/javascript">
                  var c_end = '<?php echo $bbb_json; ?>'  //インプットボックスの列数
                  var ary = JSON.parse('<?php echo addslashes($aaa_json) ; ?>'); //配列をphpから持ってくる場合

                  function func1() {  //金融資産
                    var d3=document.getElementById("abox3").value*1;   //保険金用
                    var d2=document.getElementById("abox2").value*1;   //不動産用
                    var d1=document.getElementById("abox1").value*1;   //金融資産用
                    document.getElementById("totallall").setAttribute("value", d3+d2+d1);
                    for(var i=2;i<=c_end+1;i++){
	                  var b_id="mbox"+(i-1);    //金融資産用
	                  var b_id2="rbox"+(i-1);    //不動産用
	                  var b_id3="ibox"+(i-1);    //保険用
	                  var b_id4="tbox"+(i-1);    //合計用
	                  //var dddd=document.getElementById("abox1").value;   //金融資産用
	                  
	                  if (d1 > 0){
	                	// 数値が入っている場合
		                document.getElementById(b_id).value= d1*(ary[i-2][2]/ary[i-2][3]);
		                document.getElementById(b_id).removeAttribute("disabled");
		                document.getElementById(b_id).style.color = "black";
		                //合計値の修正
		                var mmm=document.getElementById(b_id).value*1;
		                var rrr=document.getElementById(b_id2).value*1;
		                var iii=document.getElementById(b_id3).value*1;
		                document.getElementById(b_id4).setAttribute("value", mmm+rrr+iii);
	                  }else{
		                // 空欄の場合
		                document.getElementById("mbox"+(i-1)).value='';
		                document.getElementById(b_id).setAttribute("disabled", true);
		                document.getElementById(b_id).style.color = "White";
		                //合計値の修正
		                var mmm=document.getElementById(b_id).value*1;
		                var rrr=document.getElementById(b_id2).value*1;
		                var iii=document.getElementById(b_id3).value*1;
		                document.getElementById(b_id4).setAttribute("value", mmm+rrr+iii);
	                  }  
	                }
	                
                  }
                  
                  
                  function func2() {   //不動産
                    var d3=document.getElementById("abox3").value*1;   //保険金用
                    var d2=document.getElementById("abox2").value*1;   //不動産用
                    var d1=document.getElementById("abox1").value*1;   //金融資産用
                    document.getElementById("totallall").setAttribute("value", d3+d2+d1);
                    for(var i=2;i<=c_end+1;i++){
	                  var b_id="mbox"+(i-1);    //金融資産用
	                  var b_id2="rbox"+(i-1);    //不動産用
	                  var b_id3="ibox"+(i-1);    //保険用
	                  var b_id4="tbox"+(i-1);    //合計用
	                  var dddd=document.getElementById("abox2").value;   //不動産用
	                  if (dddd == ""){
              		    // 空欄の場合
	                	document.getElementById(b_id2).setAttribute("disabled", true);
		                document.getElementById(b_id2).style.color = "White";
		                document.getElementById(b_id2).value='';
		                //合計値の修正
		                var mmm=document.getElementById(b_id).value*1;
		                var rrr=document.getElementById(b_id2).value*1;
		                var iii=document.getElementById(b_id3).value*1;
		                document.getElementById(b_id4).setAttribute("value", mmm+rrr+iii);
	                  }else{
		                // 数値が入っている場合
		                document.getElementById(b_id2).removeAttribute("disabled");
		                document.getElementById(b_id2).style.color = "black";
		                document.getElementById(b_id2).value= dddd*(ary[i-2][2]/ary[i-2][3]);
		                //合計値の修正
		                var mmm=document.getElementById(b_id).value*1;
		                var rrr=document.getElementById(b_id2).value*1;
		                var iii=document.getElementById(b_id3).value*1;
		                document.getElementById(b_id4).setAttribute("value", mmm+rrr+iii);
	                  }  
	                }
                  };
                  
                  function func3() {   //死亡保険金
                    var d3=document.getElementById("abox3").value*1;   //保険金用
                    var d2=document.getElementById("abox2").value*1;   //不動産用
                    var d1=document.getElementById("abox1").value*1;   //金融資産用
                    document.getElementById("totallall").setAttribute("value", d3+d2+d1);
                    for(var i=2;i<=c_end+1;i++){
	                  var b_id="mbox"+(i-1);    //金融資産用
	                  var b_id2="rbox"+(i-1);    //不動産用
	                  var b_id3="ibox"+(i-1);    //保険用
	                  var b_id4="tbox"+(i-1);    //合計用
	                  var dddd=document.getElementById("abox3").value;   //保険金用
	                  if (dddd == ""){
              		    // 空欄の場合
	                	document.getElementById(b_id3).setAttribute("disabled", true);
		                document.getElementById(b_id3).style.color = "White";
		                document.getElementById(b_id3).value='';
		                //合計値の修正
		                var mmm=document.getElementById(b_id).value*1;
		                var rrr=document.getElementById(b_id2).value*1;
		                var iii=document.getElementById(b_id3).value*1;
		                document.getElementById(b_id4).setAttribute("value", mmm+rrr+iii);
	                  }else{
		                // 数値が入っている場合
		                document.getElementById(b_id3).removeAttribute("disabled");
		                document.getElementById(b_id3).style.color = "black";
		                document.getElementById(b_id3).value= dddd*(ary[i-2][2]/ary[i-2][3]);
		                //合計値の修正
		                var mmm=document.getElementById(b_id).value*1;
		                var rrr=document.getElementById(b_id2).value*1;
		                var iii=document.getElementById(b_id3).value*1;
		                document.getElementById(b_id4).setAttribute("value", mmm+rrr+iii);
	                  }  
	                }
                  };
                  
                  function func10(){
                      for(var i=2;i<=c_end+1;i++){
                        var i_id="ibox"+(i-1);    //保険金用
                        var r_id="rbox"+(i-1);    //不動産用
                        var m_id="mbox"+(i-1);    //金融資産用
                        var t_id="tbox"+(i-1);    //合計用
                        var d3=document.getElementById(i_id).value*1;   //保険金用
                        var d2=document.getElementById(r_id).value*1;   //不動産用
                        var d1=document.getElementById(m_id).value*1;   //金融資産用
                        document.getElementById(t_id).setAttribute("value", (d1+d2+d3));
                      }
                  }
                  
                </script>

                {{ csrf_field() }}
                <!--<input type="submit" class="btn btn-primary" value="計算開始">-->
 
 
 
                
               <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
               
  
    <body>
  
  <div><br></div>
  
  
  <canvas id="myBarChart"></canvas>  <!--棒グラフ描画-->
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js"></script>
    
  <script type="text/javascript">
    
    var c_end = '<?php echo $bbb_json; ?>'  //インプットボックスの列数
    var ary = JSON.parse('<?php echo addslashes($aaa_json) ; ?>'); //配列をphpから持ってくる場合
    
    function pushTwoDimensionalArray(array1, array2, axis){  //配列の結合関数 縦方向は第3引数省略,横方向に結合する場合は第3引数(axis)を1に指定する。
      if(axis != 1) axis = 0;
      if(axis == 0){  //　縦方向の追加
        for(var i = 0; i < array2.length; i++){
          array1.push(array2[i]);
        }
      }
      else{  //　横方向の追加
        for(var i = 0; i < array1.length; i++){
          Array.prototype.push.apply(array1[i], array2[i]);
        }
      }
    }
    
    function deleteRow(arr, row) { //配列の行を削除する関数
       arr = arr.slice(0); // make copy
       arr.splice(row - 1, 1);
       return arr;
    }
  
    function comma(num) {  //数値をカンマ区切りする関数
       return String(num).replace( /(\d)(?=(\d\d\d)+(?!\d))/g, '$1,');
    }
  
  $(function(){
    // ボタンがクリックされたら
    $("#btn").on("click", function(event){
      // DB格納用のデータを配列aに整理
      var fid = '<?php echo $fid_json; ?>' //ファミリーID
      var fid = fid.replace(/"/g, ""); //ダブルクォーテーション外し
      var fname = '<?php echo $fname_json; ?>'  //ファミリー名
      var fname = fname.replace(/"/g, ""); //ダブルクォーテーション外し
      var hai = '<?php echo $hai_json; ?>' //配偶者有無
      var hai = hai.replace(/"/g, ""); //ダブルクォーテーション外し
      var abox1d=0;  //金融資産合計を再計算
      var abox2d=0;  //不動産合計を再計算
      var abox3d=0;  //保険合計を再計算
      
      var i=1;
      var a=[
            [fid,fname,ary[i-1][0],ary[i-1][1],'金融資産',null,null,null,null,null,null,$('#mbox'+i).val(),null]
          ]; 
      abox1d=abox1d+$('#mbox'+i).val()*1;
      if($('#rbox'+i).val()>0){
          var b=[
            [fid,fname,ary[i-1][0],ary[i-1][1],'不動産',null,null,null,null,null,null,$('#rbox'+i).val(),null]
            ]; 
          pushTwoDimensionalArray(a, b);
          abox2d=abox2d+$('#rbox'+i).val()*1;
      }
      if($('#ibox'+i).val()>0){
          var b=[
            [fid,fname,ary[i-1][0],ary[i-1][1],'死亡保険金',null,null,ary[i-1][0],null,null,null,$('#ibox'+i).val(),null]
            ]; 
          pushTwoDimensionalArray(a, b);
          abox3d=abox3d+$('#ibox'+i).val()*1;
      }
          
      if(c_end>1){
          for(var i=2;i<=c_end;i++){
                if($('#mbox'+i).val()>0){
                   var b=[
                         [fid,fname,ary[i-1][0],ary[i-1][1],'金融資産',null,null,null,null,null,null,$('#mbox'+i).val(),null]
                   ];
                   pushTwoDimensionalArray(a, b);
                   abox1d=abox1d+$('#mbox'+i).val()*1;
                }
                if($('#rbox'+i).val()>0){
                   var b=[
                         [fid,fname,ary[i-1][0],ary[i-1][1],'不動産',null,null,null,null,null,null,$('#rbox'+i).val(),null]
                   ]; 
                   pushTwoDimensionalArray(a, b);
                   abox2d=abox2d+$('#rbox'+i).val()*1;
                }
                if($('#ibox'+i).val()>0){
                   var b=[
                         [fid,fname,ary[i-1][0],ary[i-1][1],'死亡保険金',null,null,ary[i-1][0],null,null,null,$('#ibox'+i).val(),null]
                   ]; 
                   pushTwoDimensionalArray(a, b);
                   abox3d=abox3d+$('#ibox'+i).val()*1;
                } 
          }
      }
      document.getElementById('abox1').setAttribute("value", abox1d);  //各相続人の合計を計算し、金融資産合計を再インプット
      document.getElementById('abox2').setAttribute("value", abox2d);  //各相続人の合計を計算し、不動産合計を再インプット
      document.getElementById('abox3').setAttribute("value", abox3d);  //各相続人の合計を計算し、保険合計を再インプット
      document.getElementById('totallall').setAttribute("value", abox1d+abox2d+abox3d);  //各資産の合計を計算し、合計を再インプット
      //もし、金融資産がなくて不動産などがある場合、最初の行に無理やり付けた相続人ID1の金融資産を削除する
      if(($('#abox1').val()+$('#abox2').val()+$('#abox3').val() > 0) && $('#mbox1').val()==""){
           a=deleteRow(a, 1) 
      }
      
      
    $.ajax({
       type: "GET", //　POSTでも可
       url: "asset", //　送り先(コントローラー、ルーティングを行っていないとエラーになる)
       
       data: {'fid':fid,'kkk':a,'finfo':ary,'hai':hai}, //　渡したいデータ
       
       dataType : "json" //　データ形式を指定
       //scriptCharset: 'utf-8' //　文字コードを指定
        }).done(function (param) {
            //各相続人の納税額など表示
            
            document.getElementById('hht').setAttribute("value", "保険金の非課税額（万円）");
            document.getElementById('kit').setAttribute("value", "課税価格（万円）");
            document.getElementById('nzt').setAttribute("value", "納税額（万円）");
            document.getElementById('sst').setAttribute("value", "正味承継資産（万円）");
            for(var i=1;i<=c_end;i++){
                //var kik='kibox'+ i;
                document.getElementById('hhbox' + i).setAttribute("value", Number(param[0][i].toFixed()).toLocaleString());
                document.getElementById('kibox' + i).setAttribute("value", Number(param[1][i].toFixed()).toLocaleString());
                document.getElementById('nzbox' + i).setAttribute("value", Number(param[2][i].toFixed()).toLocaleString());
                var ss=document.getElementById("tbox"+i).value*1-param[2][i]*1;
                document.getElementById('ssbox' + i).setAttribute("value", Number(ss.toFixed()).toLocaleString());
            }
            document.getElementById('hhall').setAttribute("value", Number(param[0][0].toFixed()).toLocaleString());
            document.getElementById('kiall').setAttribute("value", Number(param[1][0].toFixed()).toLocaleString());
            document.getElementById('nzall').setAttribute("value", Number(param[2][0].toFixed()).toLocaleString());
            
            //正味承継資産の計算
            var ss0=document.getElementById("totallall").value*1-param[2][0];
            document.getElementById('ssall').setAttribute("value", Number(ss0.toFixed()).toLocaleString());
            var a1=document.getElementById("abox1").value*1; //金融資産の合計
            var a2=document.getElementById("abox2").value*1; //不動産の合計
            var a3=document.getElementById("abox3").value*1; //生命保険の合計
            
            //グラフ描画
            var memo=10 ** ((param[2][0]*1+ss0*1).length + 1); //資産合計額の文字数
            var ctx = document.getElementById("myBarChart");
            
            if( myBarChart=='[object Object]' ){
              myBarChart.destroy();
            }
            myBarChart = new Chart(ctx, {
            
            type: 'bar',
            data: {
            labels: ['資産', '負債・正味承継資産'],
            datasets: [
             {
               label: '不動産',
               data: [a2, 0],
               backgroundColor: "rgba(240,220,185,0.9)"
             },{
               label: '保険金',
               data: [a3, 0],
               backgroundColor: "rgba(218,230,232,0.9)"
             },{
               label: '金融資産',
               data: [a1, 0],
               backgroundColor: "rgba(225,186,117,0.9)"
             },{
               label: '正味承継資産',
               data: [0, ss0],
               backgroundColor: "rgba(222,124,119,0.9)"
             },{
               label: '相続税',
               data: [0, param[2][0].toFixed()],
               backgroundColor: "rgba(115,115,115,0.9)"    
             }
            ]
            },
            options: {
            title: {
              display: true,
              text: '相続財産の内訳（ファミリー合計）'
            },
            scales: {
              xAxes: [{
                    stacked: true, //積み上げ棒グラフにする設定
                    categoryPercentage:1.0 //棒グラフの太さ
              }],
              yAxes: [{
                stacked: true, //積み上げ棒グラフにする設定
                ticks: {
                  suggestedMax: 10,
                  suggestedMin: 0,
                  stepSize: memo,
                  callback: function(value, index, values){
                    return  value +  '万円'
                  }
                }
              }]
            },
            }
            });
            
            
            //alert( param );
            
            
            
            
            
        }).fail(function (xhr,textStatus,errorThrown) {
            alert('error');
        });

    });
  }); 

  </script>
</body>            


  

                
                
                
            </div>
        </div>
    </div>
@endsection

