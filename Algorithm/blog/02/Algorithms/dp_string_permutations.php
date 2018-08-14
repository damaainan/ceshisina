<?php

class Permutations{

  
  public static function calculatePermutations($s){
      $result = [];
      $ht = self::buildGreqTable($s);
      self::getPerms($ht, "", strlen($s), $result);
      return $result;
  }
  
  protected static function buildGreqTable($s){
      $hm = []; //hash map
      for($i = 0; $i < strlen($s); $i++){
         $chr = $s[$i];
         if(!isset($hm[$chr])){
             $hm[$chr] = 0;
         }
         $hm[$chr] ++;
      }
      return $hm;
  }
  
  protected static function getPerms($map, $prefix, $remaining, &$result){
      #base case:
      if($remaining == 0){
         $result[] = $prefix;
         return;
      }
      
      foreach($map as $chr => $cnt){
         if($cnt > 0){
              $map[$chr] = $cnt - 1;
              self::getPerms($map, $prefix . $chr, $remaining - 1, $result);
              $map[$chr] = $cnt;
         }
      }
  }
  
  
}

print_r(Permutations::calculatePermutations('abc'));


