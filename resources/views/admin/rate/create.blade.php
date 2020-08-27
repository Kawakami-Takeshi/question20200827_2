<?php
   //familiesテーブルからデータ取得
   $fid=3; //familiesテーブルのid
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
                <table id="myTBL">
                    <thread>
                            <tr>
                               <th width="150"></th>
                               <th>合計</th>
 
                            </tr>
                    </thread>
                    <tbody>
                        <tr>
                            <td width="150">法定相続割合</td>
                            <td>100 / 100</td>
                            
                        </tr>
                        <tr>
                            <td width="150">金融資産（万円）</td>
                            <td>
                                <input type="text" oninput="value = value.replace(/[^0-9]+/i,'');" id="abox1" class="form-control" name="fname" value="" onchange="func1()">
                            </td>
                        </tr>
                        <tr>
                            <td width="150">不動産（万円）</td>
                            <td>
                                <input type="text" oninput="value = value.replace(/[^0-9]+/i,'');" id="abox2" class="form-control" name="fname" value="" onchange="func2()">
                            </td>
                        </tr>
                        <tr>
                            <td width="150">死亡保険金（万円）</td>
                            <td>
                                <input type="text" oninput="value = value.replace(/[^0-9]+/i,'');" id="abox3" class="form-control" name="fname" value="" onchange="func3()">
                            </td>
                        </tr>
                         <tr>
                            <td width="150">合計（万円）</td>
                            <td>
                                <input type="text" style="background-color:#000000;font-size:15;color:#FFFFFF;border:none" id="totallall" class="form-control" name="fname" value=0>
                            </td>
                        </tr>
                    </tbody>
                               <script type="text/javascript">
                                 var c_end = '<?php echo $bbb_json; ?>'  //変数をphpから持ってくる場合
                                 var ary = JSON.parse('<?php echo addslashes($aaa_json) ; ?>'); //配列をphpから持ってくる場合
                                 
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
		                           input1.setAttribute("name","fname"); 
		                           input1.setAttribute("value","");
		                           input1.setAttribute("disabled",true);
		                           input1.setAttribute("onchange", "func10()");
		                           tblObj.rows[2].cells[i].appendChild(input1);
		                           tblObj.rows[3].insertCell(-1);// インプット用のセルを追加（不動産）
                                   var input1 = document.createElement("input");
                                   input1.setAttribute("type","text"); 
		                           input1.setAttribute("class","form-control");
		                           input1.setAttribute("id","rbox"+ary[i-2][0]);
		                           input1.setAttribute("name","fname"); 
		                           input1.setAttribute("value","");
		                           input1.setAttribute("disabled",true);
		                           input1.setAttribute("onchange", "func10()");
		                           tblObj.rows[3].cells[i].appendChild(input1);
		                           tblObj.rows[4].insertCell(-1);// インプット用のセルを追加（死亡保険金）
                                   var input1 = document.createElement("input");
                                   input1.setAttribute("type","text"); 
		                           input1.setAttribute("class","form-control");
		                           input1.setAttribute("id","ibox"+ary[i-2][0]);
		                           input1.setAttribute("name","fname"); 
		                           input1.setAttribute("value","");
		                           input1.setAttribute("disabled",true);
		                           input1.setAttribute("onchange", "func10()");
		                           tblObj.rows[4].cells[i].appendChild(input1);
		                           //合計値,非入力セル
                                   tblObj.rows[5].insertCell(-1);
                                   var input1 = document.createElement("input");
                                   input1.setAttribute("type","text"); 
		                           input1.setAttribute("class","form-control");
		                           input1.setAttribute("id","tbox"+ary[i-2][0]);
		                           input1.setAttribute("name","fname"); 
		                           input1.setAttribute("value",0);
		                           input1.setAttribute("style","background-color:#000000;font-size:15;color:#FFFFFF;border:none");
		                           input1.setAttribute("disabled",true);
		                           tblObj.rows[5].cells[i].appendChild(input1);
		                           
                                 }
                              
                               </script>
                </table>
                
                <p></p>
                <p>※1　数値のみ入力可能です。<br>
                ※2　合計資産額を入力すれば、各相続人への遺産配分額を入力することが出来ます。<br>
                ※3　各相続人への遺産配分額は法定相続割合が初期値となっています。</p>
                
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
	                  var dddd=document.getElementById("abox1").value;   //金融資産用
	                  
	                  if (dddd == ""){
              		    // 空欄の場合
	                	document.getElementById(b_id).setAttribute("disabled", true);
		                document.getElementById(b_id).style.color = "White";
		                document.getElementById(b_id).setAttribute("value", "");
		                //合計値の修正
		                var mmm=document.getElementById(b_id).value*1;
		                var rrr=document.getElementById(b_id2).value*1;
		                var iii=document.getElementById(b_id3).value*1;
		                document.getElementById(b_id4).setAttribute("value", mmm+rrr+iii);
	                  }else{
		                // 数値が入っている場合
		                document.getElementById(b_id).removeAttribute("disabled");
		                document.getElementById(b_id).style.color = "black";
		                document.getElementById(b_id).setAttribute("value", dddd*(ary[i-2][2]/ary[i-2][3]));
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
		                document.getElementById(b_id2).setAttribute("value", "");
		                //合計値の修正
		                var mmm=document.getElementById(b_id).value*1;
		                var rrr=document.getElementById(b_id2).value*1;
		                var iii=document.getElementById(b_id3).value*1;
		                document.getElementById(b_id4).setAttribute("value", mmm+rrr+iii);
	                  }else{
		                // 数値が入っている場合
		                document.getElementById(b_id2).removeAttribute("disabled");
		                document.getElementById(b_id2).style.color = "black";
		                document.getElementById(b_id2).setAttribute("value", dddd*(ary[i-2][2]/ary[i-2][3]));
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
		                document.getElementById(b_id3).setAttribute("value", "");
		                //合計値の修正
		                var mmm=document.getElementById(b_id).value*1;
		                var rrr=document.getElementById(b_id2).value*1;
		                var iii=document.getElementById(b_id3).value*1;
		                document.getElementById(b_id4).setAttribute("value", mmm+rrr+iii);
	                  }else{
		                // 数値が入っている場合
		                document.getElementById(b_id3).removeAttribute("disabled");
		                document.getElementById(b_id3).style.color = "black";
		                document.getElementById(b_id3).setAttribute("value", dddd*(ary[i-2][2]/ary[i-2][3]));
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
                  
                  function func11(){
                        document.getElementById("ibox3").setAttribute("value", 100);
                      
                  }
                
                　
                  
                </script>

                {{ csrf_field() }}
                <input type="submit" class="btn btn-primary" value="計算開始">
 
 
 
                
               
               <script src="{{ asset('js/app.js') }}" defer></script>
  
                <body>
                   <script>
                       $(function() {
                              alert('jquery');
                       });
　                 </script>

                </body>

    

                
                
                
            </div>
        </div>
    </div>
@endsection

