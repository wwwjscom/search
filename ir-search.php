<?

@$func		= $_GET['func'];
@$input 	= $_GET['input'];
@$name 		= $_GET['name'];

$funcClass = new Functions;
$funcClass->setName($name);
$funcClass->setInput($input);
$funcClass->run($func);


class Functions {

	private $input 	= null;
	private $name 	= null;

	public function run($funcName)
	{
		switch($funcName)
		{
			case "addConcept":
				$this->addConcept();
				break;
			case "getAllConcepts":
				// In this case, name represents the persons username.
				$this->getAllConcepts($this->name);
				break;
			case "getConceptData":
				// In this case, name represents the username/filename.xml
				//$this->getConceptData($this->getName());
				$this->getConceptData($this->name);
				break;
			case "javaTest":
				$java = shell_exec('java JavaLink1');
				echo($java);
		}
	}

	/* Returns the concept data.  This allows the user to edit it.  However,
	 * currently we display the XML to the user, we will need to write a funcion
	 * which will convert the XML back to plain text.  Or can we just use the DOM
	 * or simpleXML object in php...?  Hmmm.... */
	public function getConceptData($filePathAndName)
	{

		$myFile = "concepts/".$this->getName().".xml";

		$fh = fopen($myFile, 'r') or die("can't open file");
		$contents = fread($fh, filesize($myFile));
		echo $contents;
		fclose($fh);
	}

	/* Returns all concepts that a given user has created over time.
	 * This list is returned to flex and the filenames are displayed
	 * to the user so that they can drag-drop them when building queries. */
	public function getAllConcepts($name)
	{
		//define the path as relative
		$path = "concepts/".$name."";

		//using the opendir function
		$dir_handle = @opendir($path) or die("Unable to open $path");

		//echo "Directory Listing of $path<br/>";

		print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
		print "<files>";
		//running the while loop
		while ($file = readdir($dir_handle)) 
		{
			if($file == "." || $file == "..")
			{
				// Do nothing - don't want to display these.
			} else {
				$file = substr($file,0,strlen($file)-4);
			   print "<file>$file</file>";
			}
		}
		print "</files>";

		//closing the directory
		closedir($dir_handle);
	}

	public function setName ($name)
	{
		$this->name = $name;
	}

	public function getName()
	{
		return $this->name;
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

		for($i = 0; $inputArrayLength > $i; $i++)
		{
			$c = $inputArray[$i];	
			
			/* If c is an open bracket */
			if($c == "(" || $c == "[")
			{
				if($c_all != null)
				{
					$xmlArray[] = "$c_all</term>\n";
				} else if($term_i != null) {
					$xmlArray[] = "$term_i</term>\n";
				}

				$xmlArray[] = "<bracket>\n";
				$term_i = null;
				$c_all 	= null;
				$p_type = "Bracket";
			} else if ($c == ")" || $c == "]") {
			/* If c is an close bracket */

				if($c_all != null)
				{
					$xmlArray[] = "$c_all</term>\n";
				} else if($term_i != null) {
					$xmlArray[] = "$term_i</term>\n";
				}
				
				$xmlArray[] = "</bracket>\n";
				$term_i = null;
				$c_all 	= null;
				$p_type = "Bracket";
			} 
		

			else if ($this->isLogic($c_all) == true) {
			/* If c is a logic */
				
				if($c_all != null || $term_i != null)
				{
					$xmlArray[] = "$term_i</term>\n";
				}
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
			$xmlArray[] = "$c_all</term>\n";
		}

		$xmlArray[] = "</concept>\n";

		$xmlString = $this->errorCorrect($xmlArray);

		$myFile = "concepts/".$this->getName().".xml";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $xmlString);
		fclose($fh);
		
	}

	public function errorCorrect($xmlArray)
	{
		$string = null;
		$prev_term = null;
		for($i = 0; sizeOf($xmlArray) > $i; $i++)
		{
			if($prev_term == "<term>" && $xmlArray[$i] == "</term>\n")	
			{
				$j = $i-1;
				$xmlArray[$j] = "";
				$xmlArray[$i] = "";
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
