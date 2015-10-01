<?php
/*
*   class.class.php
*
*   This class provides methods to construct and write a class file.
*/

class tableClass {
	public $classname;          // Name of our class.
    public $serveraddress;      // IP Address for MySQL connection.
    public $serverusername;     // Username for MySQL connection.
    public $serverpassword;     // Password for MySQL connection.
    public $databasename;       // Name of database.
    public $tablename;          // Name of table within database.
    public $variables;
    public $primarykey;         // Field/publiciable set to primary key.
    private $filename;          // Path to file we're going to write.
    private $filepath;          // Directory+filename to save file to.
    private $filedate;          // Today's date.
    private $output;            // Text to write to file.
    public $filesrequired;      // Any files required. (default: class.database.php)

	public function __construct($sName = "newclass", $sDatabase = "", $sTable = "", $sPrimaryKey = "", $sServerAddress = "localhost", $sServerUsername = "root", $sServerPassword = "",$vars=array()) {
		// Construction of class
        $this->classname = $sName;
        $this->variables = $vars;
        $this->filedate = date("l, M j, Y - G:i:s T");
        $this->filesrequired = null; //array("class.database.php");                     // Add any other required files here.
        $this->filename = "$this->classname.class.php";
        $this->filepath = realpath(dirname(__FILE__) . "/../output/") . "/$this->filename";
        $this->databasename = $sDatabase;
        $this->serveraddress = $sServerAddress;
        $this->serverusername = $sServerUsername;
        $this->serverpassword = $sServerPassword;
        $this->tablename = $sTable;
        $this->primarykey = $sPrimaryKey;
	}

     public function setFile($sPath = "", $sFilename = "") {
        // Sets the path and/or the filename to use for the class.
        if($sPath != "") {
            $this->filepath = $sPath;
        }

        if($sFilename != "") {
            $this->filename = $sFilename;
        }
    }

    public function setRequired($aFiles) {
        // Sets the required files to passed array.
        $this->filesrequired = $aFiles;
    }

    public function getRequired() {
        // Returns text to require all files in filesrequired array.
        $sRet = "// Files required by class:\n";
        if(!empty($this->filesrequired)) {
            foreach($this->filesrequired as $file) {
                $sRet .= "require_once(\"$file\");\n";
            }
        } else {
            $sRet .= "// No files required.\n";
        }

        $sRet .= "\n";

        return($sRet);
    }

    public function getHeader() {
        // Returns text for a header for our class file.
        $sRet  = "<?php\n";
        $sRet .= "/*******************************************************************************
* Class Name:       $this->classname
* File Name:        $this->filename
* Generated:        $this->filedate
*  - for Table:     $this->tablename
*  - in Database:   $this->databasename
* Created by: 
********************************************************************************/\n\n";
        $sRet .= $this->getRequired();
        $sRet .= "// Begin Class \"$this->classname\"\n";
        $sRet .= "class $this->classname extends modObject\n{\n";

        return($sRet);
    }

    public function getFooter() {
        // Returns text for a footer for our class file.
        $sRet = "}\n";
        $sRet .= "// End Class \"$this->classname\"\n?>";

        return($sRet);
    }

    public function getVariables() {
        // public function to return text to declare all the variables in the class.
        $sRet =     "// Variable declaration\n";
        $sRet .=    "protected \$$this->primarykey; // Primary Key\n";
        foreach($this->variables as $variable) {
            // Loop through variables and declare them.
            if($variable != $this->primarykey) {
                // Variable is not primary key, so we'll add it.
                $sRet .= "protected \$$variable;\n";
            }
        }
        // Add variable for connection to database.
        //$sRet .= "protected \$database;\n\n";

        return($sRet);
    }

    public function getConstructorDestructor() {
        // public function to create the class constructor and destructor.
        $sRet  = "// Class Constructor\npublic function __construct() {\n";
        //$sRet .= "\$this->database = new Database();\n\$this->database->SetSettings(\"$this->serveraddress\", \"$this->serverusername\", \"$this->serverpassword\", \"$this->databasename\");\n}\n\n";
        
        /*$sRet .= "\$this->database = sm_Database::getInstance();\n\n}\n\n";
        $sRet .=  "// Class Destructor\npublic function __destruct() {\n";
        $sRet .= "unset(\$this->database);\n}\n\n";*/
        $sRet .= "parent::__construct();\n}\n\n";
        $sRet .=  "// Class Destructor\npublic function __destruct() {\n";
        $sRet .= "parent::__destruct();\n}\n\n";

        return($sRet);
    }

    public function getGetters() {
        // public function to create all the GET methods for the class.
        $sRet =  "// GET Functions\n";

        // Create the primary key function.
        $sRet .= "public function get$this->primarykey() {\n";
        $sRet .= "return(\$this->$this->primarykey);\n}\n\n";

        // Loop through variables to create the functions.
        foreach($this->variables as $variable) {
            // Loop through variables and declare them.
            if($variable != $this->primarykey) {
                // Variable is not primary key, so we'll add it.
                $sRet .= "public function get$variable() {\n";
                $sRet .= "return(\$this->$variable);\n}\n\n";
            }
        }

        return($sRet);
    }

    public function getSetters() {
        // public function to create all the SET methods for the class.
        $sRet =  "// SET Functions\n";

        // Create the primary key function.
        $sRet .= "public function set$this->primarykey(\$mValue) {\n";
        $sRet .= "\$this->$this->primarykey = \$mValue;\n}\n\n";

        // Loop through variables to create the functions.
        foreach($this->variables as $variable) {
            // Loop through variables and declare them.
            if($variable != $this->primarykey) {
                // Variable is not primary key, so we'll add it.
                $sRet .= "public function set$variable(\$mValue) {\n";
                $sRet .= "\$this->$variable = \$mValue;\n}\n\n";
            }
        }

        return($sRet);
    }

    public function getSelect() {
        $sRet  = "public function select(\$mID) { // SELECT Function\n// Execute SQL Query to get record.\n";
        $sRet .= "\$sSQL =  \"SELECT * FROM $this->tablename WHERE $this->primarykey = \$mID;\";\n";
        $sRet .= "\$oResult =  \$this->database->query(\$sSQL);\n";
        $sRet .= "\$oRow=null;\nif(\$oResult) {\n\$oRow = (object)\$oResult[0];\n}\nelse {\n\$err=\$this->database->getError();\nif(\$err!=\"\"){\ntrigger_error(\$err);\n}\nreturn false;\n}\n";
        //$oResult = \$this->database->result;\n\$oRow = mysql_fetch_object(\$oResult);\n\n";
        $sRet .= "// Assign results to class.\n";
        $sRet .= "\$this->$this->primarykey = \$oRow->$this->primarykey; // Primary Key\n";
        // Loop through variables.
        foreach($this->variables as $variable) {
        	if($variable!=$this->primarykey)
           		 $sRet .= "\$this->$variable = \$oRow->$variable;\n";
        }
        $sRet .="return true;\n";
        $sRet .= "}\n\n";

        return($sRet);
    }

    public function getInsert() {
        $sRet  = "public function insert() {\n";
        $sRet .= "\$this->$this->primarykey = NULL; // Remove primary key value for insert\n";
        $sRet .= "\$sSQLData = \$this->toArray();\n";
        $sRet .= "unset(\$sSQLData['$this->primarykey']);\n";
        $sRet .= "\$oResult = \$this->database->save('$this->tablename',\$sSQLData);\n";
        $sRet .= "\$this->$this->primarykey = \$this->database->getLastInsertedId();\n";
        $sRet .= "return \$this->$this->primarykey != NULL;\n}\n\n";
        return($sRet);
    }
    
    public function getInsert_long() {
    	$sRet  = "public function insert() {\n";
    	$sRet .= "\$this->$this->primarykey = NULL; // Remove primary key value for insert\n";
    	$sRet .= "\$sSQL = \"INSERT INTO $this->tablename (";
    	$i = "";
    	foreach($this->variables as $variable) {
    		if($variable!=$this->primarykey)
    		{
    			$sRet .= "$i`$variable`";
    			$i = ", ";
    		}
    
    	}
    	$i = "";
    	$sRet .= ") VALUES (";
    	foreach($this->variables as $variable) {
    		if($variable!=$this->primarykey)
    		{
    			$sRet .= "$i'\$this->$variable'";
    			$i = ", ";
    		}
    	}
    	$sRet .= ");\";\n";
    	$sRet .= "\$oResult = \$this->database->query(\$sSQL);\n";
    	$sRet .= "\$this->$this->primarykey = \$this->database->getLastInsertedId();\n";
    	$sRet .= "return \$this->$this->primarykey != NULL;\n}\n\n";
    	return($sRet);
    }

    public function getUpdate() {
        $sRet  = "function update(\$mID) {\n";
        $sRet .= "\$oResult = NULL;\n";
        $sRet .= "\$sSQLData = \$this->toArray();\n";
        $sRet .= "unset(\$sSQLData['$this->primarykey']);\n";
        $sRet .= "\$where['$this->primarykey']=\$this->$this->primarykey;\n";
        $sRet .= "\$oResult = \$this->database->save('$this->tablename',\$sSQLData,\$where);\n";
        $sRet .= "return \$oResult != NULL;\n}\n\n";
        return($sRet);
    }
    
    public function getUpdate_long() {
    	$sRet  = "function update(\$mID) {\n";
    	$sRet .= "\$oResult = NULL;\n";
    	$sRet .= "\$sSQL = \"UPDATE $this->tablename SET ($this->primarykey = '\$this->$this->primarykey'";
    	// Loop through variables.
    	foreach($this->variables as $variable) {
    		//$sRet .= ", $variable = '\" . mysql_real_escape_string(\$this->$variable, \$this->database->link) . \"'";
    		if($variable!=$this->primarykey)
    			$sRet .= ", `$variable` = '\$this->$variable'";
    	}
    	$sRet .= ") WHERE $this->primarykey = \$mID;\";\n";
    	$sRet .= "\$oResult = \$this->database->query(\$sSQL);\n";
    	$sRet .= "return \$oResult != NULL;\n}\n\n";
    	return($sRet);
    }

    public function getDelete() {
        // Creates the delete function.
    	
        $sRet = "public static function delete(\$mID) {\n";
        $sRet .= "\$oResult = NULL;\n";
        $sRet .= "\$where['$this->primarykey']=\$mID;\n";
        $sRet .= "\$oResult = \$this->database->delete('$this->tablename',\$where);\n";
       	$sRet .= "return \$oResult != NULL;\n}\n\n";

        return($sRet);
    }
    
    public function getDelete_long() {
    	// Creates the delete function.
    	 
    	$sRet = "public function delete(\$mID) {\n";
    	$sRet .= "\$oResult = NULL;\n";
    	$sRet .= "\$sSQL = \"DELETE FROM $this->tablename WHERE $this->primarykey = \$mID;\";\n";
    	$sRet .= "\$oResult = \$this->database->query(\$sSQL);\n";
    	$sRet .= "return \$oResult != NULL;\n}\n\n";
    
    	return($sRet);
    }

    public function createClass($bEcho = 0, $bWrite = 1) {
        // Creates class file.

        // Generate the file text.
        $sFile  =   $this->getHeader() .        $this->getVariables() .
                    $this->getConstructorDestructor() .   $this->getGetters() .
                    $this->getSetters() .       $this->getSelect() .
                    $this->getInsert() .        $this->getUpdate() .
                    $this->getDelete() .        $this->getFooter();
     
     

        // If we are to display the file contents to the browser, we do so here.
        if($bEcho) {
            echo "";
            highlight_string($sFile);
            echo "<br><br><br>Output save path: $this->filepath";
        }

        // If we are to write the file (default=TRUE) then we do so here.
        if($bWrite) {
            // Check to see if file already exists, and if so, delete it.
            if(file_exists($this->filename)) {
                unlink($this->filename);
            }

            // Open file (insert mode), set the file date, and write the contents.
            $oFile = fopen($this->filepath, "w+");
            fwrite($oFile, $sFile);
        }

        // Exit the function
        return($this->filepath);
    }
}
?>
