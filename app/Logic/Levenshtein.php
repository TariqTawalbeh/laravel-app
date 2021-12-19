<?php 

namespace App\Logic;

class Levenshtein
{
	
	protected string $strg1;
	protected string $strg2;

	public set_words($word1, $word2){
		$this->strg1 = $word1;
		$this->strg2 = $word2;
	}

	public function levenshtein_dis($offset1, $offset2){
		$str1 = substr($this->strg1, $offset1);
		$str2 = substr($this->strg2, $offset2);

		if (empty($str1))
        {
            return strlen($str2);
        }

		if (empty($str2))
        {
            return strlen($str1);
        }
   
        $replacements = levenshtein_dis(
              substr($str1, 1), substr($str2, 1))
              + replacements_count($str1[0],$str2[0]);
  
        $inserts = levenshtein_dis(
                         $str1, substr($str2, 1))+ 1;
  
        $deletetions = levenshtein_dis(
                         substr($str1, 1), $str2)+ 1;
          
        return min_edits_count($replacements, $inserts, $deletetions);
	}

	private function replacements_count($s1, $s2)
    {
        
        return ($s1 == $s2) ? 0 : 1;
    }
  
    private function min_edits_count($replacements, $inserts, $deletetions)
    {
        
        return min($replacements, $inserts, $deletetions);
    }

}