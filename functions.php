<?php
    
/*   example of input string: Before some \struct[*1]{*2} after the struct
     
     the array $arguments[] contains: [0]: the string before the structure.  es: $argument[0] = "before some "
                                      [1]: first argument.                   es: $argument[1] = "*1"
                                      [.]: ectera                            es: $argument[2] = "*2"
                                    [n+1]: the string is after the strcture. es: $argument[3] = "after the struct"
     
     The DataBase contains:  (IDENTIFIER)         es. \frac
                             (STRUCTURE)          es. \frac{*}{*}
                             (SEQUENSE OF WORDS)  es. the fraction of *1 over *2
     
     Example:
      if the string is:              "considering \frac{\sqrt[2]{x}}{\alpa} as the asintotic variable"
      it will be convertend in:      "considering the fraction of \sqrt[2]{x} over \alpa as the asintotic variable."
 
     In the nexts loops also others structures such \sqrt[2]{x} and \alpa will be processed.
*/


function decompositor($input, $row, $position, &$until, &$arguments, $result, $exption){
	
    
    //this take the first part of the string before the \struct. It will set $argument[0].
    for($i = 0; $i< $position ; $i++) $string_temp = $string_temp.array_shift($input);
	$arguments[]= $string_temp;
	
    //it defines which kinds of simbols it reconazes as brakets.
    $bras = array(0 =>"(",1=>"[", 2=>"{");
    $kets = array(0 =>")",1=>"]", 2=>"}");
    
    //Back up in case the structure in the database does not matches.
    $restore_input = $input;
    $restore_arguments = $arguments;
    
    //to stop the look if it finds the correct structure in the DB.
    $done = 1;

    /*this loop finds which combination of arguments are associate with a structure in the DB.
      In the database: \example{}{}   -> each of them have different sequence of words.
                       \example_{}^{}
                       \example[]{}
    */
    
    while($row  = $result->fetch_assoc()){
        
        if($done){
            //From the DB it gets the sequence of words that corrisponde to a certain structure.
            $row_correct = $row;
            $structure = str_split($row['structure']);
            
            //$array_out is the output of this functions
            $array_out = array(0=>str_split($row[$_GET["language"]]), 1=> $row['exeptions_arguments']);
            
            $input = $restore_input;
            $arguments = $restore_arguments;
            $i = 0;
        
            while($i<count($structure) && $i < 400){ //$i < 400 is just to avoid infinit look if something goes wrong
                if(($structure[$i] == $input[0] || $structure[$i]== "*"  || (in_array($structure[$i],array_merge($bras,$kets)) && ($input[0] != "^" || $input[0] != "_" )))){
            
                    if(in_array($structure[$i],array_merge($bras,$kets)) && ($input[0] != "^" || $input[0] != "_" ) && $structure[$i] != $input[0] && in_array($input[0],array_merge($bras,$kets)) == false){
               
                        while($input[0] == " "){ array_shift($input);}
                
                        if($input[0]=="\\"){
                            $e = 1;
                            //this look will the end of the current braket {--(*{*}*)-->}. Even if it contains sub-brakets.
                            while($input[$e] != " " &&   $input[$e] != "\\" && $input[$e] != "_" && $input[$e] != "^"){
                                if($input[$e] == "{"){
                                    $indicies = 1;
                                    for($rr = 0; $rr < count($input); $rr++){
                                        $e++;
                                        if($input[$e] == "{") $indicies ++;
                                        if($input[$e] == "}") $indicies --;
                                        //it ends the for look (setting rr big enough) when it found the correct closing braket
                                        if($indicies == 0 ) $rr = 10000; //break
                                    }
                            
                                }
                            $e++;
                            }
                            
                            array_splice($input, $e, 0, $structure[$i+2]);
                            //è qui il problema \cos \frac{}{}
                    
                        }else{
                            array_splice($input, 1, 0, $structure[$i+2]);
                        }
                        array_unshift($input, $structure[$i]);
                    }
            
            
            if($i == (count($structure)-1)) $done = 0;//fine last while loop

        
            $string_temp = ' '; $enter_in_next_while=0;
        
            if($structure[$i] == '*'){
            
                for($j =0; $j <count($bras); $j++){
               
                    if($temp == $bras[$j] ){
                        $bra = $j;
                        $enter_in_next_while=1;
                    }
                    if($temp == " " ){  //per quando non si metto le parentesi \*** 2 2
                        while($temp == " ") $temp =array_shift($input);
                    
                            $temp_1="";
                            if($temp == "\\"){
                                $i++;
                                while($temp != " "){
                                    $temp_1=$temp_1.$temp;
                                    $temp =array_shift($input);
                                }
                            }
                        
                        $string_temp = $string_temp.$temp_1.$temp;
                    
                        $j = 100;
                        $i+= 1;
                    }
                
                if(is_numeric($temp)){  //per quando non c'è ne parentesi ne spazio \***2 2
                    
                    //$string_temp = $string_temp.$temp;
                    //$j = 100;$i++;
                }
                if(($structure[$i-2]== "_" || $structure[$i-2]== "^")  && $enter_in_next_while==0 && $j==(count($bras)-1)){
                    
                    $string_temp = $string_temp.$temp;
                    
                    $j = 100;$i++;
                }

                //|| is_numeric($temp)
			}
			//inzio selezione argomento.
			$indicies = 1; $n = 0;
            if($enter_in_next_while==1) {
			     while($indicies != 0 && $n < 400){
			        $string_temp = $string_temp.array_shift($input);
				    $n++;
				
				    if($input[0] == $bras[$bra]) $indicies++;
				    if($input[0] == $kets[$bra]) $indicies--;
			
				
			    }
                $enter_in_next_while = 0;
            }
			//$i += $n;
			$arguments[]=$string_temp;
            
            
		}else{
			$temp = array_shift($input);
            
           
		}
            //recupero cancello da qui
    }else{
                $i = 10000;
    }
        // fino a qui!
    
       
		$i++;
        
        //  qui chiudere if
    }
    }//here
        
    }
    
    $row = $row_correct;
    //prendo parte rimanente dopo struttura dell input
	$string_temp = ' '; 
	$legh = count($input);
	for($e = 0; $e < $legh ; $e++){
		$string_temp = $string_temp.array_shift($input);
	}
	$arguments[]= $string_temp;
    
    return $array_out;
}

function position_word($arguments,$row, $sequense_text_db, $expetion){

    	$output =  str_split($arguments[0]); $output[]=' ';
 
        //eccezzioni
        if($expetion){
            $code = "<?php ".$expetion." ?>";
            eval("?> $code <?php ");
        }

    	$i = 0;
    	while($i < count($sequense_text_db)){
    	        if($sequense_text_db[$i]== '*'){
    	        	$i++;
    	        	
    	        	$temp = str_split($arguments[$sequense_text_db[$i]]);
    	        	for($e=0; $e<count($temp);$e++){
    	        		$output[] = $temp[$e];
    	        	}
    	        	
    	        }else{
    	        	
    	        	$output[] = $sequense_text_db[$i];
    	        }
    	 	$i++;
    	}       
         //agigunto quello che rimane della striga.
        $temp = str_split($arguments[count($arguments) -1 ]);
        for($e=0; $e<count($temp);$e++){
    	        		$output[] = $temp[$e];
        }

    		
return $output;
}

function addslash($array, $add_slash){ //aggiunge uno \ prima di questi = - + ! |

    for($v = 0; $v< count($array) ; $v++){  //** modifico gli = -> \=
        for($m=0;$m<count($add_slash);$m++){
            if($array[$v] == $add_slash[$m]){
                $temp_array = array_slice($array,0,$v);
                $temp_array[] = ' '; $temp_array[] = '\\'; $temp_array[] = $add_slash[$m] ; $temp_array[] = ' ';
                $temp_array1 = array_slice($array, $v+1);
                $array = array_merge($temp_array,$temp_array1);
                $v += 2 ;
            }
        }
    }
    return $array;
}

function deletespaces($array, $delete_spaces){ //it delete space before [ { _ ^ (
    for($v = 0; $v< count($array) ; $v++){
        for($m=0;$m<count($delete_spaces);$m++){
            if($array[$v] == $delete_spaces[$m]){
                $i=1;
                while($array[$v-$i]==" "){
                    
                    $i++;
                }
                $i--;
                $temp_array = array_slice($array,0,$v-$i); //+- qualcosa
                $temp_array1 = array_slice($array, $v);
                $array = array_merge($temp_array,$temp_array1);
                $v -= ($i -1) ;
               
            }
        }
    }
    
    return $array;
}

function deletespaces_after($array, $delete_spaces){ //it delete space before [ { _ ^ (
    for($v = 0; $v< count($array) ; $v++){
        for($m=0;$m<count($delete_spaces);$m++){
            if($array[$v] == $delete_spaces[$m]){
                $i=1;
                while($array[$v+$i]==" "){
                    
                    $i++;
                }
                //$i--;
                $temp_array = array_slice($array,0,$v+1); //+- qualcosa
                $temp_array1 = array_slice($array, $v + $i);
                $array = array_merge($temp_array,$temp_array1);
                $v ++;
                
                
                
            }
        }
    }
    
    return $array;
}

function addspaces($array, $simbols){
    for($v = 0; $v< count($array) ; $v++){
        for($m=0;$m<count($simbols);$m++){
            if($array[$v] == $simbols[$m]){
            
                
                $temp_array = array_slice($array,0,$v);
                $temp_array[]=" ";
                $temp_array1 = array_slice($array, $v);
                $array = array_merge($temp_array,$temp_array1);
                $v++;
                
            }
        }
    }
    
    return $array;

}

function addspaces_after($array, $simbols){
    
    for($v = 0; $v< count($array) ; $v++){
        for($m=0;$m<count($simbols);$m++){
            if($array[$v] == $simbols[$m]){
                
                $temp1   = array_slice($array, 0, $v+1);
                $temp1[] = " ";
                $temp2 = array_slice($array, $v+1);
                $array = array_merge($temp1, $temp2);
                
            }
        }
    }
    
    return $array;
}

function convert_bad_things($array,$language){
    include 'conf.php';
    while($string_in[0] == " "){
        $string_in = substr($string_in,1);
    }
    
    $sql1 = "SELECT * FROM `convert` WHERE `in`='".$string_in."' ";
    
    $result = $conn->query($sql1);
    
    
    if ($result->num_rows > 0) {
        $row  = $result->fetch_assoc();
        $string_out = " ".$row["out_".$language];
        
    }else{
        
    }

    
    return $string_out;
}

function printm($array){
    
    echo "<br>->";
    for($ff=0; $ff<count($array); $ff++)
        echo$array[$ff]." ";
    }

function two_array_are_equal($array1, $array2){ // it take also the string as a input.
    
    if(is_string($array2)){
        $array2= str_split($array2);
    }
    $test = 0;
    
    if(count($array1) == count($array2) && is_array($array1)){
        for($i=0; $i<count($array1); $i++){
            if($array1[$i]!= $array2[$i]) $test = 1;
        }
    }else{
        $test=1;
    }

    if($test == 0) return true; else return false;
    
}

function brakets($array, $simbols_braket){
    
    $letters = array("a","b","c","d","e", "f", "g", "h", "i", "l", "m", "n");
    
    //convertire || con |
    $t = 0;
    while(strpos(implode($array), "\left")&& $t<15){
        
        $t++;
        
        
        for($h=0; $h<count($array); $h++){
            if(two_array_are_equal(array_slice($array, $h, 5 ), "\left")){
               $position = $h;
               $h = 10000;
               }
        }
        
        
          $temp_array1 = array_slice($array, 0, $position);
        
        
          for($i =0; $i<count($simbols_braket); $i++){  //position + 5 mi da {
                if($array[$position + 5] == $simbols_braket[$i] ||($array[$position + 6] == "\\" && $array[$position + 7] == $simbols_braket[$i])){
                    
                    $for_slash = 6;
                    //it deletes first part of the array
                    if($array[$position + 6] == "\\" && $array[$position + 7] == $simbols_braket[$i]) $for_slash+=2;
                    
                    for($x =0; $x<($position + $for_slash); $x++) array_shift($array);
                    
                    //this will write the proper \braket@??
                    $temp_array2=array(0 =>" ",1=>"\\", 2=>"b", 3=>"r", 4=> "a", 5=>"k", 6=>"e", 7=> "t");
                    $temp_array2[] = "@"; $temp_array2[] = $letters[$i];
                   
                    //conto quanti altri \left and \right ci sono.
                    $indicies=1; $position_right_end = 0;
                
                    
                    for($x=0; $x<(count($array)-6); $x++){
                        
                        if(two_array_are_equal(array_slice($array, $x, 5), "\left" )) $indicies++;
                        if(two_array_are_equal(array_slice($array, $x, 6), "\\right")) $indicies--;
               
                        if($indicies==0){
                            
                            $temp_array3 = array_slice($array, 0, $x);
                            $temp_array3[] = "}";
                   
                            for($e =0; $e<count($simbols_braket); $e++){  //position + 5 mi da {
                                if($array[$x + 6] == $simbols_braket[$e] ||($array[$x + 7] == "\\" && $array[$x + 8] == $simbols_braket[$e])){
                                    $for_slash = 7;
                                    //cancello prima parte dell'array:
                                    if($array[$x + 7] == "\\" && $array[$x + 8] == $simbols_braket[$e]) $for_slash+=2;
                                    for($f =0; $f<($x + $for_slash); $f++) array_shift($array);
                                
                                    $temp_array2[]=$letters[$e]; $temp_array2[]= "{";
                                    
                                }
                            }
                            
                            
                            
                            $i = 10000;
                            $x = 100000;
                        }
                    }

                }
          
            
          }
        
        $array = array_merge($temp_array1,$temp_array2,$temp_array3, $array);
        
    }
    
    return $array;
}

function singol_braket($array){
    
    $letters_open = array(0 =>"b",1=>"i", 2=>"c", 3=>"d", 4=>"e", 5=>"l");
    $letters_close= array(0 =>"b",1=>"i", 2=>"f", 3=>"g", 4=>"h", 5=>"m");
    $with_slash_open = array(0 =>"**",1=>"||", 2=>"(", 3=>"[", 4=>"{",5=>"<<");
    $with_slash_close =array(0 =>"**",1=>"||", 2=>")", 3=>"]", 4=>"}",5=>">>");
    $with_out = array(0 =>"**",1=>"||",2=>"(");
    
    for($i=0; $i<(count($array)-2); $i++){
                
     if(($array[$i] == "\\" && in_array($array[$i +1], $with_slash_open)) || in_array($array[$i], $with_out)){    $expetion =1; //c'è \ o no?
         
        

     if(in_array($array[$i], $with_out)) $expetion =0;
         
        $temp1 = array_slice($array, 0, $i);
        $temp2 = str_split(" \braket@");
        $braket = array_search($array[$i + $expetion], $with_slash_open );
        $temp2[] = $letters_open[$braket];
         
        $index = 0;
        $indicies = 0;
         
        for($e=($i+$expetion); $e<(count($array)-1); $e++){
            
         
            //it needs to look only inside one braket unless this appen: { \| }{\|} -> \braket{ \braket{--}-->}
            if($braket == 0 || $braket == 1){
            //devo escludere caso in cui \| perchè non c'è differenza tra aprie e chiudere.
            if($indicies == 0) $indicies=1;
            if($array[$e] == "}"|| $array[$e] == ")"||$array[$e] == "]") $index--;
            if($array[$e] == "{"|| $array[$e] == "("||$array[$e] == "[") $index++;
            
            //this reconaze this (..|..)
            if($index < 0) {
                $array[$i+$expetion] = " ";
                $e = 100000; //break;
            }
            
              
            if($index == 0){ //altrimenti elabora questi casi |...(...|..)
                if($e==($i+$expetion))$e++;//perchè altrimenti vede se stesso e va in loop.
                if(($array[$e] == "\\" && $array[$e+1] == $with_slash_close[$braket]) || $array[$e]== $with_slash_close[$braket]) $indicies = 0;
            }

            }else{
               
                if(($array[$e-1] == "\\" && $array[$e] == $with_slash_open[$braket]) || $array[$e-1] == $with_out[$braket]) $indicies++;
                
                if(($array[$e-1] == "\\" && $array[$e] == $with_slash_close[$braket]) || $array[$e-1]== $with_slash_close[$braket]) $indicies--;
                    
            }
            
            if($indicies == 0){
                $expetion_end = -1;
                if($array[$e]== "\\") $expetion_end =0;
                
                    $temp3 = array_slice($array, $i + $expetion + 1, ($e  - 1 -($i + $expetion )));
                    $temp3[] = "}";
                    $temp4 = array_slice($array, $e + $expetion_end +2 - count($array));
                    $temp2[] = $letters_close[$braket];
                    $temp2[] = "{";
                
                $array = array_merge($temp1,$temp2,$temp3, $temp4);
                
                
                $e = 100000;  //break;
                
            }

       }

   }
            }
    
    
    return $array;
}

function before_round_brackets($array){
    //remeber to leave a space before the words!!
    $shearch = array(" f", " cos", " sen", " sin", " tan", "\cos", "\sin", "\\tan", "cot", "\cot", "\csc","\sec"," \delta"," h");
    $replace = array(" \\function{effe}", " \cos", " \sin", " \sin", " \\tan", " \cos", " \sin", " \\tan", "\cot", " \cot", " \csc"," \sec", " \dirac"," \\function{h}");

    for($i=0;$i<(count($array)-2);$i++){
        
        $done = 0;
        for($s=0; $s<count($shearch); $s++){
            $temp = str_split($shearch[$s]);
            if(array_slice($array, $i, count($temp)) == $temp){
                $index_for_replace = $s;
                $e = count($temp); $z = 0;
                if($array[$i + $e] == "^"||$array[$i + $e] == "_"){
                    $z++;
                    $indicies = 1;
                    if($array[$i + $e + $z] == "{"){
                    while( $indicies != 0){
                        $z++;
                        if($array[$i +$e +$z] == "{") $indicies++;
                        if($array[$i +$e +$z] == "}") $indicies--;
                        
                    }
                    }
                    
                    
                    $z++;
                } //cerco per la parentesi.
                
                $bra = array("(", "\(", "\left(", "\left\(");
                
                //For each element in the array $bra it checks if there is corrispondece.
                for($l=0; $l<count($bra); $l++){
                    
                    //from string to array
                    $temp = str_split($bra[$l]);
                    
                    if(array_slice($array, $i + $e + $z, count($temp)) == $temp){
                        $done = 1;
                        $bra_start = count($temp);
                        
                        $indicies = 1;
                        $bra_end = 0;
                        while($indicies !=0){
                            if($array[ $i + $e + $z + $bra_start + $bra_end] == "(") $indicies++;
                            if($array[ $i + $e + $z + $bra_start + $bra_end] == ")") $indicies--;
                            $bra_end ++;
                            if($bra_end > 500){ $indicies = 0; $done=0;}
                        }
                        $delete = 0;
                        $end = array("\\","\\right","\\right\\");
                        //ok
                        for($k=0; $k<count($end); $k++){
                            $temp = str_split($end[$k]);
                            if(array_slice($array, $i + $e + $z + $bra_start + $bra_end -1 -count($temp),count($temp)  ) == $temp){
                                $delete= count($temp);
                            }
                        }
                        
                        
                        break;
                        //$l = 1000;
                    }
                    
                }
                
                
               }
        }
       
        
        if($done == 1){
            unset($temp_3);
            
            $temp_1 = array_slice($array, 0, $i);
            $temp_2 = str_split($replace[$index_for_replace]);
            $temp_3 = array_slice($array, $i + $e, $z); $temp_3[]= "{";
            $temp_4 = array_slice($array, $i + $e +$z + $bra_start, $bra_end-$delete-1); $temp_4[]= "}";
            $temp_5 = array_slice($array, $i + $e +$z + $bra_start + $bra_end);
            $array  = array_merge($temp_1,$temp_2,$temp_3,$temp_4,$temp_5);
            $i+=2;
        }
        
    }
 
    return $array;
}

function powers_indicies($array){
    include 'conf.php';
    //find ^ or _
    for($i=0; $i<count($array); $i++){
        
        //prima prendo l'argomento che punta.
        if($array[$i] == "^" || $array[$i] == "_"){
        if($array[$i] == "^") $indentity = "power"; else $indentity = "subscript"; //So I know in which struct convert it.
            $add_i =0;
            if($array[$i + 1] == "{"){
                $add_i = 1;
                $indicies = 1;
                for($e=2; $e<(count($array)-$i); $e++){
                    if($array[$e + $i] == "{") $indicies++;
                    if($array[$e + $i] == "}") $indicies--;
                    if($indicies == 0){
                        $after = $e;
                        $e = 10000;
                    }
                }
            }else{
                $after = 2; $add_i=0;
            }
            
            $a = 1;
            while($array[$i-$a]!= " " && $array[$i-$a]!= "\\" && $array[$i-$a]!= "{"&& $array[$i-$a]!= "("&& $array[$i-$a]!= ","  && ($i-$a) >= 0){
                
                if($array[$i - $a ] == "}"){
                    $indicies = 1;
                    for($e=1; $e<$i; $e++){
                        
                        if($array[$i - $a - $e] == "}") $indicies++;
                        if($array[$i - $a - $e] == "{") $indicies--;
                        if($indicies == 0){
                            $a += $e ;
                            $e = 10000;
                        }
                    }
                }
                
                $a++;
               
                
            }
            
            if($array[$i -$a] == "\\"){
                $identifier = "\\";
                
                for($z=1;$z < $a; $z++){
                    if($array[$i -$a +$z] == "{"|| $array[$i -$a +$z] == "["||$array[$i -$a +$z] == "(" || $array[$i -$a +$z] == "_"|| $array[$i -$a +$z] == "^"){
                        $z=10000;
                    }else{
                        $identifier = $identifier.$array[$i -$a +$z];
                    }
                }
                
                $sql1 = "SELECT * FROM `struct` WHERE `identifier` LIKE  '%\\".$identifier."%' ORDER BY CHAR_LENGTH(structure) DESC ";
                
                $result = $conn->query($sql1);
                $do = "ok";
                if ($result->num_rows > 0) {
                 while($row = $result->fetch_assoc()) {
                     if(strpos($row["structure"], $array[$i])) $do = "no";
                 }
                    
                }else{
                    $do = "ok";
                }
                
               
                
                
            }
                        
            if($array[$i -$a] == " " || $array[$i -$a] == "{"  )$do = "ok";
        
            
            if($do=="ok"){
                $temp1 = array_slice($array, 0, ($i-$a ));
                $temp1[] = " "; $temp1[] = "\\";
                $temp2 = str_split($indentity);
                $temp2[] = "{";
                $temp3 = array_slice($array, ($i-$a), $a);
                $temp3[] = "}";  $temp3[] = "{";
                $temp4 = array_slice($array, $i + 1 + $add_i, $after-(1+$add_i));
                $temp4[] = "}";
                $temp5 = array_slice($array, $i + $after + $add_i);
               
                $array = array_merge($temp1,$temp2,$temp3,$temp4, $temp5);
                
            }

            
           
                 }
        
    }
   
    for($i=0; $i<count($array); $i++) if($array[$i] == ".." ) $array[$i] = " ";
   
    return $array;
}

function substitution($array){
    $braket = array("\\|","|","\langle","\\rangle");
    $replace= array(
                //This is a cool idea. Since it is an array, a "letter" can contain more caracters.
                //we can distinguish \| and | that are displayed differently. \|->|| while |->|
                array("\\","||"),
                array("**"),
                array("\\","<<"),
                array("\\",">>"),
              );
    
    for($s=0; $s<(count($array)); $s++){
        for($b=0; $b<count($braket); $b++){
            $lenght = count(str_split($braket[$b]));
            $temp = array_slice($array, $s, $lenght);
            
            if(two_array_are_equal($temp,$braket[$b])){
                $array= array_merge(array_slice($array, 0, $s), $replace[$b],array_slice($array, $s + $lenght));
            }
        }
    }
    return $array;
}

/*This is the first function that you will call from external file.
  The input are an array with the LaTex strings and the setup for the DB (it is ok if empty ""),
  The output is an array with the natual language form of the formulas.
*/

function master($array, $conf_DB){
    //ho inserito questa funzione html_entity_decode()? prima di mandarmi l'array! toglie i simobli HTML &..
    
    if(!isset($_GET["language"])) $_GET["language"] = "en";            //default language
   
    include 'conf.php';                                                //reset database connection
    $temp = array(" ",".."); $array=array_merge($temp,$array,$temp);   //add space at the end and at the begging
    
    //for the next functions see the flowchart to undestand why
    //it need to consider the difference between  \| and | . Since one is the norm and the other is the absolute value.
    $array = substitution($array);
    //this allow to consider those simbols as \structures
    $array = addslash($array, array("=","-", "+","!","<",">","/","|","&"));
    //to make the parser easier
    $array = deletespaces($array, array("[","{","_","^","(","\\","**"));
    $array = deletespaces_after($array, array("^","_"));
    $array = before_round_brackets($array);      //manage situation as f(x) cox(x) sin^2(x)
    $array = addspaces($array, array("\\"));     //this add a space before
    //this convert brakets as: \left{ ** \right} into  \braket@??{**}. The ?? are indentifier of the type of braket
    $array = brakets($array, array(".","**", "(", "[", "{", ")", "]", "}", "||", "<", ">"));
    
    //$array = singol_braket($array);  //it still contains some bugs. Cases as <a|b> <c|d> it
                                       //will crasch because it will break the grammar of brakets
    
    $array = addspaces_after($array, array("(",")","{",","));
    $array = powers_indicies($array);
    
    
    //cerco tutto gli \\ e vedo cosa a quale struttura si riferisce.
    while (in_array("\\", $array)){
        
        $legh = count($array); //lunghezza array e non stringa
        
        //cancello le variabili perchè altrimenti rientrano le ciclo while.
        unset($struct, $sting_struct, $output, $output_array, $arguments, $input, $string_temp);
        $position = array_search('\\', $array);    //rilevo posizione dello \ (indica inzio formula)
        
        for($j = $position; $j<$legh ; $j++){      //cerco gli elementi ({[ per isolare \STRUCT
            
            if($array[$j] == '{' || $array[$j]== '[' || $array[$j]== '(' || $array[$j]== '^' || $array[$j]== '_' || $array[$j]== ' ' || is_numeric($array[$j])){
                
                for($z = $position; $z<=$j ; $z++) $struct[] = $array[$z];
                if($array[$j] == "[" || $array[$j] == "_" || $array[$j] == "^" ) $ecption = 0; else $ecption = 1;   //gestisco eccezioni come \**[]{}
                $until = $j;                       //Dove si trova il primo delimitatore
                $j = $legh + 1;                    //break loop
                
                
                for($i = 0; $i< count($struct) - $ecption ; $i++){  //converto STRUT in stringa (per cercare in DB)
                    $sting_struct = $sting_struct.$struct[$i];
                }
                
                $sql1 = "SELECT * FROM `struct".$conf_DB."` WHERE `identifier`=  '\\".$sting_struct."' ORDER BY CHAR_LENGTH(structure) DESC ";
                
                $result = $conn->query($sql1);
               
                if ($result->num_rows > 0) {
                    
                    $exption = " ";
                    $array_out = decompositor($array, $row, $position, $until, $arguments, $result, $exption);
                    
                   
                    $output = position_word($arguments, $row, $array_out[0], $array_out[1]); //riarrangia posizione argomenti. Usando db.
                
                }else{//cosa fare se non trova nulla nel DB??
                    
                    $array[$position] = ' ';  // trasfroma il simbolo \ di \***{} in ***{}
                    $output = $array;
                }
                
            }else{//se non c'è delimitatore uno di quelli indicati
                
                //____________
                
                
            }
        }
        
        $array = $output;
        
    }
    
    //$array = convert_bad_things($array,$_GET["language"]);
    
    return $array;
}






?>
