# RjakesSimpleFM

Wrapper/facade that extends Jeremiah Small's SimpleFM class for communicating with FileMaker Server:

https://github.com/soliantconsulting/SimpleFM
 
This class is a convenience wrapper to SimpleFM that offers quick and easy CRUD operations, as well as a very easy method to execute FileMaker scripts from PHP.  It's helpful for programmers that don't want or need to build out the query parameters that the FileMaker Web Pushinging Engine requires.
 
Author Roger Jacques - roger@rjakes.com

## System Requirements

* PHP 5.3+
* FileMaker Server 12+


## Quickstart via Trivial Examples
The quickstart files below will work with the FMServer_Example file that installs with FileMaker Server, and, they will work with fuelPHP without modification. The best way to get the required classes is to add an entry to the composer.json file that you will find at the root of your project folder after creating a new fuelPHP project.

```
    "require": {
        "php": ">=5.3.3",
        "composer/installers": "~1.0",
        "fuel/docs": "1.7.2",
        "fuel/core": "1.7.2",
        "fuel/auth": "1.7.2",
        "fuel/email": "1.7.2",
        "fuel/oil": "1.7.2",
        "fuel/orm": "1.7.2",
        "fuel/parser": "1.7.2",
        "fuelphp/upload": "2.0.1",
        "monolog/monolog": "1.5.*",
        "michelf/php-markdown": "1.4.0",
		"rjakes/rjakessimplefm": "dev-master"
    },
```

Then, from your project directory, run:

```
composer.phar update
```
    

These examples will work in any project or framework that can load the required classes, with minimal modification (the controller below has a bit of fuelPHP specific code.)

### Base Model

```
<?php
/*
 * This is a base model that exists in a single location to instantiate 
 * the connection to FileMaker.
 * All other models are extended from this model.
 *
 * There are other ways to instantiate an object, and if you understand them
 * then you probably don't need a quickstart from me.
 */

namespace Model;

use \rjakes\RjakesSimpleFM\Facade;

class BaseModel {

    public $fmpConn = FALSE;

    public function __construct()
    {
        $hostParams    = array(
                        'hostname'     => '127.0.0.1',
                        'dbname'        => 'FMServer_Sample',
                        'username'      =>  'admin',
                        'password'      =>  '');
        $this->fmpConn  = new Facade($hostParams);
    }

}
```
### Model
```
<?php
/**
* This model extends the base class and the database connection.
*
**/

namespace Model;

use \Model\BaseModel;

class Language extends BaseModel {

    public function __construct()
    {
       // don't forget to call the parent constructor
       // else you will not get your connection object
       parent::__construct();

        // Set a default layout for this model, this can be overridden as needed.
        // The layout is a required parameter for all database interactions,
        // it sets the context, controling which table is queried,
        // dictating which fields are available for querying and which are 
        // returned.
       $this->fmpConn->setDefaultLayoutName('PHP Technology Test');

    }

    public function getStrings($language)
    {
        $language = 'SAMPLE_' . $language;
        
        $this->fmpConn->addWhereCriteria('LANGUAGE MATCH FIELD', $language, 'eq');
        
        // use the default layout that was set in the constructor of this class
        $result = $this->fmpConn->select();
        return $result;
    }


}
```
### Controller
```
<?php
/**
* This is a fuelPHP controller, provided just to make the example complete.
* There is nothing here that is specific to RjakesSimpleFM
 */

use \Model\Language;

class Controller_Example extends Controller
{
    public $language = FALSE;

    public function before()
    {
        $this->language = new Language(); // grab an instance of the model

    }

	public function action_index()
	{

        $results['data']['getLanguage'] = $this->language->getStrings('German');

        return Response::forge(View::forge('welcome/index',								$results));         
	}
```
### The Response
```
// Here is what you get back from the model method call in the above controller.
// This structure is provided by SimpleFM.
Array
(
    [getLanguage] => Array
        (
            [url] => http://admin:[...]@192.168.0.11:80/fmi/xml/fmresultset.xml?-db=FMServer_Sample&-lay=PHP Technology Test&LANGUAGE+MATCH+FIELD=SAMPLE_German&LANGUAGE+MATCH+FIELD.op=eq&-find
            [error] => 0
            [errortext] => No error
            [errortype] => FileMaker
            [count] => 20
            [fetchsize] => 20
            [rows] => Array
                (
                    [0] => Array
                        (
                            [index] => 0
                            [recid] => 399
                            [modid] => 13
                            [LANGUAGE MATCH FIELD] => SAMPLE_German
                            [Task Name Sample] => Sitemap-Skizze
                            [Task Start Date Sample] => 03/19/2014
                            [Task Due Date Sample] => 05/02/2014
                            [Task Completion Percentage Sample] => 80
                        )

                    [1] => Array
                        (
                            [index] => 1
                            [recid] => 400
                            [modid] => 13
                            [LANGUAGE MATCH FIELD] => SAMPLE_German
                            [Task Name Sample] => Grafiken an Anbieter senden
                            [Task Start Date Sample] => 04/04/2014
                            [Task Due Date Sample] => 04/05/2014
                            [Task Completion Percentage Sample] => 0
                        )
                 )
          )
)

```


## License

RjakesSimpleFM is free for commercial and non-commercial use, licensed under the business-friendly standard MIT license.


