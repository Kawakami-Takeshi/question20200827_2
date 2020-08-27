<?php

function i_rate($marital_status,$n_child){
    //法定相続割合の計算 n行4列(相続人ID,相続人,法定相続割合の分母,法定相続割合の分子)の配列を返す
    //marital_status　配偶者の有無
    //n_child　子供の数
    
    if($marital_status == 1){ //配偶者ありの場合
        $array=[[1,"配偶者",1,2]];
        $bunbo=2*$n_child;  //法定相続割合の分母
        for($i=2 ; $i<= $n_child + 1 ; $i++){
            $array[]=[$i,"子".($i-1),1,$bunbo];
        }
    }else{ //配偶者なしの場合
        $bunbo=$n_child;  //法定相続割合の分母
        for($i=1 ; $i<= $n_child ; $i++){
            $array[]=[$i,"子".$i,1,$bunbo];
        }
    }
    
    return($array);
    
}

//print_r(i_rate(0,5));

?>

