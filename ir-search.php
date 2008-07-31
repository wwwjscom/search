<?

$function = $_GET['function'];
//$function = "addConcept"; // NEED TO BE CHANGES LATER

$input = $_GET['input'];

$funcClass = new Functions;
$funcClass->setInput($input);
$funcClass->run($function);


class Functions {

	private $input = null;

	public function run($funcName)
	{
		switch($funcName)
		{
			case "addConcept":
				$this->addConcept();
				break;
		}
	}

	public function setInput($input)
	{
		$this->input = $input;
	}

	public function getInput()
	{
		return $this->input;
	}


	/* Concepts Class/Functions */
	public function addConcept()
	{
		$input = $this->getInput();

		/* Split the input by char */
		$inputArray = $this->splitByChar($input);

		$inputArrayLength = sizeOf($inputArray);

		/* Setup loop variables */
		$p_n 		= null;
		$term_i 	= null;
		$c 		= null;
		$c_all 		= null;
		$p_type 	= null;
		$logicArray	= $this->getLogicArray();
		$xmlArray[] = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
		$xmlArray[] = "<concept>\n";
		//$xmlArray[] = "<concept>";

		for($i = 0; $inputArrayLength > $i; $i++)
		{
			$c = $inputArray[$i];	
			//echo $c;
			
			/* If c is an open bracket */
			if($c == "(" || $c == "[")
			{
				if($c_all != null)
				{
					//$xmlArray[] = "$c_all</term>";
					$xmlArray[] = "$c_all</term>\n";
				} else if($term_i != null) {
					//$xmlArray[] = "$term_i</term>";
					$xmlArray[] = "$term_i</term>\n";
				}

				//$xmlArray[] = "<bracket>";
				$xmlArray[] = "<bracket>\n";
				$term_i = null;
				$c_all 	= null;
				$p_type = "Bracket";
			} else if ($c == ")" || $c == "]") {
			/* If c is an close bracket */

				if($c_all != null)
				{
					//$xmlArray[] = "$c_all</term>";
					$xmlArray[] = "$c_all</term>\n";
				} else if($term_i != null) {
					//$xmlArray[] = "$term_i</term>";
					$xmlArray[] = "$term_i</term>\n";
				}
				
				//$xmlArray[] = "</bracket>";
				$xmlArray[] = "</bracket>\n";
				$term_i = null;
				$c_all 	= null;
				$p_type = "Bracket";
			} 
		

			else if ($this->isLogic($c_all) == true) {
			/* If c is a logic */
				
				if($c_all != null || $term_i != null)
				{
					//$xmlArray[] = "$term_i</term>";
					$xmlArray[] = "$term_i</term>\n";
				}
				//$xmlArray[] = "<logic>$c_all</logic>";
				$xmlArray[] = "<logic>$c_all</logic>\n";

				$p_type = "Logic";
				$c_all = null;
				$term_i = null;

			/* If c is a space */
			} else if ($c == " ") {
				$isLogic = $this->isLogic($c_all);
				//echo "IsLogic: $isLogic";
				if($this->isLogic($c_all) == false)
				{
					$term_i = $c_all;
				} else if ($p_type == "Logic" || $p_type == "Bracket") {
					// Do Nothing
				}
				$xmlArray[] = $term_i." ";

				$c_all = null;
				$p_type = "Space";
			} else {
			/* otherwise c is just a term */	
				if($c_all == null && $term_i == null)
				{
					$xmlArray[] = "<term>";
				}

				$c_all = $c_all."".$c;
				$p_type = "Char";
				$term_i = null;
			}
		}

		if($c_all != null)
		{
			//$xmlArray[] = "$c_all</term>";
			$xmlArray[] = "$c_all</term>\n";
		}

		//$xmlArray[] = "</concept>";
		$xmlArray[] = "</concept>\n";

		$xmlString = $this->errorCorrect($xmlArray);
/*
$xml = new SimpleXMLElement($xmlString);
echo $xml->asXML(); 
echo $xml->concept[0];
$dom = new DomDocument();
$dom->load($xmlString);
print $dom->saveXML();

*/
$myFile = 'concepts/lawl.xml';
$fh = fopen($myFile, 'w') or die("can't open file");
fwrite($fh, $xmlString);
fclose($fh);
		
		/*
		foreach($fixedXMLArray as $key => $term)
		{
			print $term;
		}
		*/
	}

	public function errorCorrect($xmlArray)
	{
		$string = null;
		$prev_term = null;
		for($i = 0; sizeOf($xmlArray) > $i; $i++)
		{
			if($prev_term == "<term>" && $xmlArray[$i] == "</term>\n")	
			//if($prev_term == "<term>" && $xmlArray[$i] == "</term>")	
			{
				$j = $i-1;
				$xmlArray[$j] = "";
				$xmlArray[$i] = "";
				//unset($j);
				//unset($i);
			}
			$prev_term = $xmlArray[$i];
		}

		$string = implode($xmlArray);
		return $string;
	}

	public function isLogic($str)
	{
		$logicArray = $this->getLogicArray();
		if(in_array($str, $logicArray))
			return true;
		else
			return false;
	}

	public function getLogicArray()
	{
		$logicArray	= array('OR','AND');
		return $logicArray;
	}

	/* Splits a string at each character.  Returns an array containing
	 * all of these characters */
	public function splitByChar($str) {
		$myArray;
		$i = 0;
	    
		while(strlen($str) > $i) {
			$myArray[] = substr($str, $i, 1);
			$i++;
		}
		return $myArray;
	}	

}

?>
