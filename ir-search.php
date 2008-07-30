<?

$function = $_GET['function'];
$input = $_GET['input'];
//$function = "addConcept"; // NEED TO BE CHANGES LATER

$funcClass = new Functions;
$funcClass->run($function);


class Functions {

	public function run($funcName)
	{
	$Concepts = new Concepts;
		switch($funcName)
		{
			case "addConcept":
				$Concepts->addConcept();
				break;
		}
	}
}

class Concepts {

	public function addConcept()
	{
		print "<option>\n";
		print "<option><price>$input</price></option>\n";
		print "</option>\n";
	}
}

?>
