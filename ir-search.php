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
				$this->getConceptData($this->name);
				break;
			case "updateConceptData":
				$this->updateConceptData($this->name);
				break;
			case "query":
				/* First we convert all the concepts back to the terms */
				$this->query();
				break;
			case "queryLucene":
				echo $this->queryLucene();
				break;
			case "queryLucid":
				echo $this->queryLucidWeb($query);
				//echo $this->queryLucid($query);
				break;
			case "queryOracle":
				echo $this->fileRead('/mnt/kwakdata/prymek/trec67combined/progs/res.out');
				break;
		}
	}

	/* This function simply queries oracle.  The query must be written to /tmp/query.
	 * We can probably change that in the future to be accepted command line, or
	 * something easier
	 */
	public function queryLucene()
	{

		$input = stripslashes($this->getInput()); // Input is correctly received.
		$this->setInput($input);

		/* Write the query to /tmp/query - this is for querying oracle, see queryOracle() */
		/* BUG HERE!!!!! We must correctly format the query before writing it to the file */
		$luceneInput 	= "<top>\n";
		$luceneInput .= "<num> Number: 1\n";
		$luceneInput .= "<title> $input\n";
		$luceneInput .= "</top>\n";
		$this->fileWrite($luceneInput, "/tmp/query");



		$java = shell_exec('/usr/local/bin/java -cp /mnt/bigfootdata/workspace2.4/2.4-dev/:/mnt/bigfootdata/workspace2.4/trec-parse/ -server -Xmx1g org/apache/lucene/search/AdvancedSearcher -index /mnt/bigfootdata/prymek/trecindex/ -queries /tmp/query -results /tmp/res.out -quiet');
		//echo($java);
		return $java;
	}

	public function buildLuceneQuery()
	{
		$myXML = stripslashes($this->getInput()); // Input is correctly received.

		$xml = simplexml_load_string($myXML);

		$input = $this->loop($xml); // Problem is in here?
		$this->setInput($input);

		//$this->setName("FUCKKKKKK"); // Filename is correctly set
		//$this->addConcept(); // Contents are correctly written

		/* Write the query to /tmp/query - this is for querying oracle, see queryOracle() */
		/* BUG HERE!!!!! We must correctly format the query before writing it to the file */
		$luceneInput 	= "<top>\n";
		$luceneInput .= "<num> Number: 1\n";
		$luceneInput .= "<title> $input\n";
		$luceneInput .= "</top>\n";
		$this->fileWrite($luceneInput, "/tmp/query");

	}

	/* See the queryLucid comments */
	public function queryLucidWeb($query)
	{
		/* <Format Query> */
		$myXML = stripslashes($this->getInput()); // Input is correctly received.

		$xml = simplexml_load_string($myXML);

		$input = $this->loop($xml); // Problem is in here?
		$this->setInput($input);
		/* </Format Query> */

		$query = $this->getInput();

		$RESULTS_PER_PAGE = 1;

		$query = urlencode($query);

		$_resultsLoc = "/tmp/lawl";
		/* BUG -- it appaers that the rows parameter is being ignored -- makes me believe
		 * it will also ignore other parameters... */
		$url = "/usr/bin/curl -d q=$query -d rows=$RESULTS_PER_PAGE --get http://delirium:8888/focus/search/search > $_resultsLoc";
		shell_exec($url);
		$results = $this->fileRead($_resultsLoc);
		//return "Query: $query";
		return $results;
	}

	/* Deprecated by LucidWeb -- if we can get the hit.vm
	 * template file to work out then we should be able to
	 * mod everything which is returned to us, thus allowing
	 * us to completely control the results interface of Lucid */
	public function queryLucid($query)
	{
		shell_exec('sh /var/www/QueryBuilder/LucidFocusQuerier.sh');
		$results = $this->fileRead('/var/www/QueryBuilder/results_dismax.properties.out');
		return $results;
	}

	public function query()
	{
		$myXML = stripslashes($this->getInput()); // Input is correctly received.

		$xml = simplexml_load_string($myXML);

		$input = $this->loop($xml); // Problem is in here?
		$this->setInput($input);

		//$this->setName("FUCKKKKKK"); // Filename is correctly set
		//$this->addConcept(); // Contents are correctly written

		/* Write the query to /tmp/query - this is for querying oracle, see queryOracle() */
		/* BUG HERE!!!!! We must correctly format the query before writing it to the file */
		$luceneInput 	= "<top>\n";
		$luceneInput .= "<num> Number: 1\n";
		$luceneInput .= "<title> $input\n";
		$luceneInput .= "</top>\n";
		$this->fileWrite($luceneInput, "/tmp/query");
		$luceneResults = $this->queryLucene();

		$lucidResults = $this->queryLucid($input);
		//$lucidResults = $this->queryLucidWeb($input);

		/**********************
		 * Insert other query methods here
		 *********************/

		/* We do this for the time being since we can't connect to the DB for some reason.
		When we are able to connect again we shouldn't hardcode the resluts, liek we do now */

		$oracleResults = $this->fileRead('/mnt/kwakdata/prymek/trec67combined/progs/res.out');
		//$oracleResults = $this->fileRead('/tmp/res.out');

		/**********************
		 * Build the results in XML
		 **********************/
		$queryXMLResults = $this->buildQueryResults($luceneResults, $lucidResults, $oracleResults);
		echo $queryXMLResults;
	}

	/* Builds the query results as an XML list */
	public function buildQueryResults($luceneResults, $lucidResults, $oracleResults)
	{
		$results  = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
		$results .= "<results>";
		$results .= "<oracle>$oracleResults</oracle>";
		$results .= "<lucid>$lucidResults</lucid>";
		$results .= "<lucene>$luceneResults</lucene>";
		$results .= "</results>";
		return $results;
	}

	public function loop($xml)
	{
		// Opening required bracket
		$input = "( ";

		// Loop through all of the sub-nodes for the given node
		foreach($xml->children() as $child)
		{
			$label = $child->attributes()->label;
			$isFolder = $child->attributes()->isFolder;
		
			// If we are looking at a folder then we need to recursively loop through it
			if($isFolder == "true")
			{
				if($label == "ALL")
					$input .= $this->loop($child);
				else if($label == "NOT" && count($child->children())>1) {
					$input .= $label . " ";
					$input .= $this->loop($child);
				} else {
					// This pretty much doesn't do anything until we implement custom folders
					$input .= $this->loop($child);
				}
			} else {
				// Otherwise we are just looking at a normal node, so lets convert
				// that node (concept) into the terms
				//$input .= $label . " ";
				
				/******************************************************
				 ******************************************************
				 * This if condition should be removed, but NOT the content.
				 * This is used because of an error within the FLEX XML,
				 * it is a work-around for error #3
				 *****************************************************
				 *****************************************************
				 */
				if($label != "NONOTME")
				{
					$this->setName($label);
					$concept = $this->getConceptData($label, true);
					//$input .= $concept . " AND ";
					$input .= $concept . " ";
				}
			}
		}

		// Required closing bracket
		$input .= ") ";

		return $input;
	}

	public function updateConceptData($fileName)
	{
		$path = "concepts/wwwjscom";
		$pathAndFile = $path."/".$fileName.".xml";
		unlink($pathAndFile);

		$this->addConcept();
	}

	/* Returns the concept data.  This allows the user to edit it.  However,
	 * currently we display the XML to the user, we will need to write a funcion
	 * which will convert the XML back to plain text.  Or can we just use the DOM
	 * or simpleXML object in php...?  Hmmm.... 
	 *
	 *	Input: return::boolean -> should we return the data or echo it (echo is used when called by FLEX)
	 */
	public function getConceptData($filePathAndName, $return = false)
	{
		$myFile = "concepts/wwwjscom/".$this->getName().".xml";

		if($return === true)
			return $this->fileRead($myFile);
		else
			echo $this->fileRead($myFile);
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
		$this->fileWrite($this->getInput());
	}

	public function fileWrite($input, $filename = null)
	{
		if($filename != null)
			$myFile = $filename;
		else
			$myFile = "concepts/wwwjscom/".$this->getName().".xml";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $input);
		fclose($fh);
	}

	public function fileRead($myFile)
	{
		$fh = fopen($myFile, 'r') or die("can't open file");
		$contents = fread($fh, filesize($myFile));
		fclose($fh);
		return $contents;
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


class OB_FileWriter
{
	private $_filename;
	private $_fp = null;
	private $_errorHandlersRegistered = false;

	public function __construct($filename)
	{
		$this->setFilename($filename);
	}

	public function __destruct()
	{
		//Make sure no data is lost
		if($this->_fp)
			$this->end();
	}

	public function setFilename($filename)
	{
		$this->_filename = $filename;
	}

	public function getFilename()
	{
		return $this->_filename;
	}

	public function setHaltOnError($value)		
	{
		//If new state is same as old, don't do anything
		if($value === $this->_errorHandlersRegistered)
			return;


		if($value === true)
		{
			set_exception_handler(array($this,'exceptionHandler'));
			set_error_handler(array($this,'errorHandler'));	
			$this->_errorHandlersRegistered = true;
		}
		else
		{
			restore_error_handler();
			restore_exception_handler();
			$this->_errorHandlersRegistered = false;
		}
	}

	public function start()
	{
		$this->_fp = @fopen($this->_filename,'w');
		if(!$this->_fp)
			throw new Exception('Cannot open file '.$this->_filename.' for writing!');

		ob_start(array($this,'outputHandler'),1024);
	}

	public function end()
	{
		$this->_stopBuffering();
		$this->setHaltOnError(false);
	}

	private function _stopBuffering()
	{
		@ob_end_flush();
		if($this->_fp)
			fclose($this->_fp);

		$this->_fp = null;
	}

	public function outputHandler($buffer)
	{
		fwrite($this->_fp,$buffer);
	}

	public function exceptionHandler($exception)
	{
		$this->_stopBuffering();
		echo '<b>Fatal error: uncaught', $exception;
	}

	public function errorHandler($errno, $errstr, $errfile, $errline)
	{		
		$this->_stopBuffering();
		$errorNumber = E_USER_ERROR;
		switch($errno)
		{
		case E_ERROR:
			$errorNumber = E_USER_ERROR;
			break;
		case E_NOTICE:
			$errorNumber = E_USER_NOTICE;
			break;
		case E_WARNING:
			$errorNumber = E_USER_WARNING;
			break;
		}
		trigger_error("$errstr, File: $errfile line $errline",$errorNumber);
	}
}

?>
