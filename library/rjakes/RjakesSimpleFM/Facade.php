<?php
/**
 * *
 * extends Jeremiah Small's SimpleFM class for communicating with FileMaker Server
 * https://github.com/soliantconsulting/SimpleFM/tree/master/library/Soliant
 *
 * This class makes SimpleFM more friendly and convenient to programmers that do not wish to learn the full FMP URL syntax
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

    protected $defaultLayoutName   = '';
    protected $whereCriteria       = array();
    protected $sortCriteria        = array();
    protected $scriptName          = '';
    protected $scriptParameters    = array();

    public function setDefaultLayoutName($fmLayout)
    {
        $this->defaultLayoutName = $fmLayout;
        return $this;
    }

    public function getDefaultLayoutName()
    {
        return $this->defaultLayoutname;
    }

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
            throw new Exception\ErrorException('The FileMaker layout name as not been specified.');
        }

        return $this;
    }


    public function addWhereCriteria($field, $value, $op='')
    {
        $element = count($this->whereCriteria);
        $whereField = urlencode($field);
        $whereValue = urlencode($value);
        $this->whereCriteria[$element] = array('field'=>$whereField, 'value'=>$whereValue, 'op'=>$op );

        return $this;
    }

    public function getWhereCriteria()
    {
      return $this->whereCriteria;

    }

    public function setWhereCriteria($whereCriteria)
    {
        $this->whereCriteria = $whereCriteria;

        return $this;
    }


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

    public function setSortCriteria($sortCriteria)
    {
        $this->sortCriteria = $sortCriteria;
        return $this;
    }

    public function getSortCriteria()
    {
        return $this->sortCriteria;
    }


    public function delete($recId, $fmLayout='')
    {

        $this->setLayoutName($fmLayout);
        $commandArray['-delete'] = '';
        $commandArray['-recid'] = $recId;
        $this->setCommandarray($commandArray);

        $result = $this->execute();

        return $result;
    }

    public function duplicate($recId, $fmLayout='')
    {
        $this->setLayoutName($fmLayout);
        $commandArray['-dup'] = '';
        $commandArray['-recid'] = $recId;
        $this->setCommandarray($commandArray);

        $result = $this->execute();

        return $result;

    }

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

    public function insert($valueArray, $fmLayout='')
    {
        $this->setLayoutName($fmLayout);

        $commandArray = $valueArray;

        $commandArray['-new'] = "";

        $this->setCommandarray($commandArray);

        $result = $this->execute();

        return $result;

    }

    public function setScriptName($scriptName)
    {
        $this->scriptName = $scriptName;
        return $this;

    }

    public function getScriptName()
    {
        return $this->scriptName;

    }

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

    public function setScriptParameters($scriptParameters)
    {

        $this->scriptParameters = $scriptParameters;
        return $this;
    }

    public function getScriptParameters()
    {
        return $this->scriptParameters;
    }



    public function makeScriptCommand()
    {
        $command = '&-script='. urlencode($this->scriptName);

        if(! empty($this->scriptParameters))
        {
            $command .= '&-script.param=';
            foreach($this->scriptParameters as $scriptParameter)
            {

                $command .= '$'.$scriptParameter['name'].'="'.$scriptParameter['value'].'" ; ';
            }
        }

        return $command;
    }


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
