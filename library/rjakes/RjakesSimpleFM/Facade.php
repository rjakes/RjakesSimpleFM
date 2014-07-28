<?php
/**
 *
 * Extends Jeremiah Small's SimpleFM class for communicating with FileMaker Server.
 * https://github.com/soliantconsulting/SimpleFM/tree/master/library/Soliant
 *
 * This class makes SimpleFM more friendly and convenient to programmers that do not wish to learn the full FMP URL syntax.
 *
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   rjakes\RjakesSimpleFM
 * @copyright Copyright (c) 20012-2015 Roger Jacques Consulting (RJAKES LLC). (http://www.rjakes.com)
 * @author    roger@rjakes.com
 *
 */

namespace rjakes\RjakesSimpleFM;

use \Soliant\SimpleFM\Adapter;
use \Soliant\SimpleFM\Exception as Exception;


class Facade   extends Adapter
{
    /**
     * @var string
     */
    protected $defaultLayoutName   = '';

    /**
     * @var array
     */
    protected $whereCriteria       = array();

    /**
     * @var array
     */
    protected $sortCriteria        = array();

    /**
     * @var string
     */
    protected $scriptName          = '';

    /**
     * @var array
     */
    protected $scriptParameters    = array();

    /**
     * Set a default layout name, so that CRUD functions can be called without a layout name.
     * @param string $fmLayout
     * @return object of class RjakesSimpleFM::Facade
     */
    public function setDefaultLayoutName($fmLayout)
    {
        $this->defaultLayoutName = $fmLayout;
        return $this;
    }

    /**
     * Get the default FileMaker layout name.
     * @return string
     */
    public function getDefaultLayoutName()
    {
        return $this->defaultLayoutname;
    }


    /**
     * Overrides SimpleFM so that we can use a default layout.
     * @param string $fmLayout
     * @return object of class RjakesSimpleFM::Facade
     */
    public function setLayoutName($fmLayout='')
    {
        if($fmLayout !== '')
        {
            $this->layoutname = $fmLayout;
        }else{
            $this->layoutname = $this->defaultLayoutName;
        }
        if($this->layoutname === '')
        {
            throw new Exception\ErrorException('The FileMaker layout name has not been specified.');
        }
        return $this;
    }


    /**
     * Add a single where/find criteria.
     * @param string $field
     * @param string $value
     * @param string $op
     * *
     * Operators:
     *
     * - eq (equals)
     * - cn (contains)
     * - bw (begins with)
     * - ew (ends with)
     * - gt (greater than)
     * - gte (greater than or equal to)
     * - lt (less than)
     * - lte (less than or equal to)
     * - neq (not equal)
     * @return object of class RjakesSimpleFM::Facade
     */
    public function addWhereCriteria($field, $value, $op='')
    {
        $element = count($this->whereCriteria);
        $whereField = urlencode($field);
        $whereValue = urlencode($value);
        $this->whereCriteria[$element] = array('field'=>$whereField, 'value'=>$whereValue, 'op'=>$op );

        return $this;
    }


    /**
     * Return the multi dimensional array of sort criteria
     * @return array ( array($field, $value, $operator))
     */public function getWhereCriteria()
    {
      return $this->whereCriteria;

    }

    /**
     * Overwrite the where criteria array property with an array of where criteria arrays.
     * @param array $whereCriteria array( array('field' => 'someFieldName', 'value' => 'some value', 'op' => 'an operator')).
     * *
     * Operators:
     *
     * - eq (equals)
     * - cn (contains)
     * - bw (begins with)
     * - ew (ends with)
     * - gt (greater than)
     * - gte (greater than or equal to)
     * - lt (less than)
     * - lte (less than or equal to)
     * - neq (not equal)
     * @return object of class RjakesSimpleFM::Facade.
     */
    public function setWhereCriteria($whereCriteria)
    {
        $this->whereCriteria = $whereCriteria;

        return $this;
    }


    /**
     * Add a single set of sort criteria to the sort criteria array property.
     * @param string $field
     * @param string $order  order is 'ascend' or 'descend'
     * @return object of class RjakesSimpleFM::Facade
     */
    public function addSortCriteria($field, $order='')
    {

        $element = count($this->sortCriteria);
        $sortPrecedence = $element+1;

        $sortField = '-sortfield.'.$sortPrecedence.'='.urlencode($field);

        if($order !== '')
        {
            $sortOrder = '-sortorder.'.$sortPrecedence.'='.($order == 'descend'? 'descend' : 'ascend');
        }else{
            $sortOrder = '';
        }

        $this->sortCriteria[$element] = array('field'=>$sortField, 'order'=>$sortOrder);
        return $this;
    }


    /**
     * Overwrite the $sortCriteria array property with an array of sort criteria arrays.
     * @param array $sortCriteria array( array($field, $order) ) order is 'ascend' or 'descend'.
     * @return object of class RjakesSimpleFM::Facade
     */
    public function setSortCriteria($sortCriteria)
    {
        $this->sortCriteria = $sortCriteria;
        return $this;
    }

    /**
     * Get the multi dimensional array of $sortCriteria property.
     * @return array(  array($field, $order) )
     */
    public function getSortCriteria()
    {
        return $this->sortCriteria;
    }

    /**
     * CRUD function to delete a record.
     * @param string $recId This is the internal FileMaker recordID. You can get this from CRUD functions that returns database results, within the 'rows' elements
     * @param string $fmLayout Optional, if a layout has already been set with setDefaultLayoutName()
     * @return array([url] [error] [errortext] [errortype] [count] [fetchsize] [rows] => array)
     */
    public function delete($recId, $fmLayout='')
    {
        $this->setLayoutName($fmLayout);
        $commandArray['-delete'] = '';
        $commandArray['-recid'] = $recId;
        $this->setCommandarray($commandArray);

        $result = $this->execute();
        return $result;
    }


    /**
     * CRUD function to duplicate a record.
     * @param string $recId This is the internal FileMaker recordID. You can get this from CRUD functions that returns database results, within the 'rows' elements
     * @param string $fmLayout Optional, if a layout has already been set with setDefaultLayoutName()
     * @return array([url] [error] [errortext] [errortype] [count] [fetchsize] [rows] => array)
     */
    public function duplicate($recId, $fmLayout='')
    {
        $this->setLayoutName($fmLayout);
        $commandArray['-dup'] = '';
        $commandArray['-recid'] = $recId;
        $this->setCommandarray($commandArray);

        $result = $this->execute();

        return $result;

    }

    /**
     * CRUD function to update a record.
     * @param string $recId This is the internal FileMaker recordID. You can get this from CRUD function that returns database results, within the 'rows' elements
     * @param array $valueArray  array('fieldName' => 'value', 'anotherFieldName' => 'value')
     * @param string $fmLayout Optional, if a layout has already been set with setDefaultLayoutName()
     * @return array([url] [error] [errortext] [errortype] [count] [fetchsize] [rows] => array)
     */
    public function update($recId, $valueArray, $fmLayout='')
    {

        $this->setLayoutName($fmLayout);
        $commandArray = $valueArray;
        $commandArray['-edit'] = "";
        $commandArray['-recid'] = $recId;
        $this->setCommandarray($commandArray);

        $result = $this->execute();

        return $result;

    }

    /**
     * CRUD function to retrieve records. The properties of $sortCriteria, $whereCriteria,and $scriptName are used, if populated.
     * @param string $max
     * @param string $skip
     * @param string $fmLayout Optional, if a layout has already been set with setDefaultLayoutName()
     * @return array([url] [error] [errortext] [errortype] [count] [fetchsize] [rows] => array)
     */
    public function select($max='', $skip='', $fmLayout='')
    {

        $this->setLayoutName($fmLayout);
        $this->commandstring = '';
        $this->commandstring .= (! empty($max) ? '-max='.$max : '');
        $amp = empty($this->commandstring) ? '' : '&';
        $this->commandstring .= (! empty($skip) ? $amp.'-skip='.$skip : '');
        $amp = empty($this->commandstring) ? '' : '&';


        if(!empty($this->whereCriteria))
        {

            foreach ($this->whereCriteria as $criteriaArray)
            {

                $field = $criteriaArray['field'];
                $value = $criteriaArray['value'];
                $op = $criteriaArray['op'];

                $this->commandstring .= $amp. $field.'='. $value;
                $amp = '&';

                if(! empty($op))
                {
                $this->commandstring .=  '&'.$field.'.op='.$op;
                }
            }
            $this->commandstring .= '&-find';
        }else{
            $this->commandstring .= $amp .  '-findall';
        }

        if(!empty($this->sortCriteria))
        {
            foreach ($this->sortCriteria as $criteriaArray)
            {

                $field = $criteriaArray['field'];
                $order = $criteriaArray['order'];

                $this->commandstring .= '&'. $field;

                if(! empty($order))
                {
                    $this->commandstring .=  '&'.$order;
                }
            }
        }

        if(!empty($scriptName))
        {
          $this->commandstring .= $this->makeScriptCommand();
        }

        $result = $this->execute();

        return $result;
    }

    /**
     * CRUD function to insert a record.
     * @param array $valueArray = array('fieldName' => 'value', 'anotherFieldName' => 'value')
     * @param string $fmLayout Optional, if a layout has already been set with setDefaultLayoutName()
     * @return array([url] [error] [errortext] [errortype] [count] [fetchsize] [rows] => array)
     */
    public function insert($valueArray, $fmLayout='')
    {
        $this->setLayoutName($fmLayout);

        $commandArray = $valueArray;

        $commandArray['-new'] = "";

        $this->setCommandarray($commandArray);

        $result = $this->execute();

        return $result;

    }

    /**
     * Overwrite the $scriptName  property with a FileMaker script name
     * @param string $scriptName
     * @return object of class RjakesSimpleFM::Facade
     */
    public function setScriptName($scriptName)
    {
        $this->scriptName = $scriptName;
        return $this;

    }

    /**
     * Get the $scriptName property, if populated
     * @return string
     */
    public function getScriptName()
    {
        return $this->scriptName;

    }

    /**
     * Add a single named script parameter to be sent to the FileMaker script
     * @param string $name
     * @param string $value
     * @return object of class RjakesSimpleFM::Facade
     */
    public function addScriptParameter($name, $value)
    {
        $element = count($this->scriptParameters);

        $parameterName = rawurlencode($name);
        $parameterValue = rawurlencode(str_replace( '"'  ,   '\"',  $value ));

        if(! empty($parameterName))
        {
            $this->scriptParameters[$element] = array('name'=>$parameterName, 'value'=>$parameterValue);
        }

        return $this;
    }


    /**
     * Overwrite the $scriptParameters multi dimensional array property with an array of script parameter arrays
     * @param array $scriptParameters array ( array([name]=>'someParmName', [value]=>'some parm value'))
     * @return object of class RjakesSimpleFM::Facade
     */
    public function setScriptParameters($scriptParameters)
    {
        $this->scriptParameters = $scriptParameters;
        return $this;
    }

    /**
     * Get the multi dimensional $scriptParameters property
     * @return array ( array([name]=>'someParmName', [value]=>'some parm value'))
     */
    public function getScriptParameters()
    {
        return $this->scriptParameters;
    }



    /**
     * Makes the name value pairs needed to execute a FileMaker script from an http request
     * @return string
     */
    public  function makeScriptCommand()
    {
        $command = '&-script='. urlencode($this->scriptName);

        if(! empty($this->scriptParameters))
        {
            $command .= '&-script.param=';
            foreach($this->scriptParameters as $scriptParameter)
            {

                $command .= $scriptParameter['name'].'='.$scriptParameter['value'].'||||';
            }
        }
        return $command;
    }


    /**
     * Executes a FileMaker script independent of a CRUD request, using the $scriptName property and any parameters in $scriptParameters
     * @param string $script Optional if $scriptName property is set.
     * @param string $fmLayout Optional if $defaultLayoutName is set.
     * @return array([url] [error] [errortext] [errortype] [count] [fetchsize] [rows] => array)
     * @todo Throw as exception for missing layoutName or scriptName
     */
    public function executeFmScript($script = '', $fmLayout='')
    {

        if($script !== '')
        {
         $this->setScriptName($script);
        }
        $this->setLayoutName($fmLayout);

		$this->commandstring = '-findany';
        $this->commandstring .= $this->makeScriptCommand();
        $result = $this->execute();

        return $result;

    }

}
