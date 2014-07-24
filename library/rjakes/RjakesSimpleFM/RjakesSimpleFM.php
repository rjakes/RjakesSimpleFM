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


class RjakesSimpleFM   extends Adapter
{

    function delete($view, $rec_id){

        $this->setLayoutname($view);
        $command_array['-delete'] = '';
        $command_array['-recid'] = $rec_id;
        $this->setCommandarray($command_array);

        $result = $this->execute();

        return $result;
    }

    function duplicate($view, $rec_id){

        $this->setLayoutname($view);
        $command_array['-dup'] = '';
        $command_array['-recid'] = $rec_id;
        $this->setCommandarray($command_array);

        $result = $this->execute();

        return $result;

    }

    function update($view, $rec_id, $value_array){

        $this->setLayoutname($view);

        $command_array = $value_array;
        $command_array['-edit'] = "";
        $command_array['-recid'] = $rec_id;
        $this->setCommandarray($command_array);

        $result = $this->execute();

        return $result;

    }

    function select($view, $where, $sort=NULL, $max=NULL, $skip=NULL, $script=NULL){

        $this->setLayoutname($view);
        $this->commandstring = '';
        $this->commandstring .= (! empty($max) ? '-max='.$max : '');
        $amp = empty($this->commandstring) ? '' : '&';
        $this->commandstring .= (! empty($skip) ? $amp.'-skip='.$skip : '');
        $amp = empty($this->commandstring) ? '' : '&';


        if(!empty($where->criteria)){

            foreach ($where->criteria as $criteria_array){

                $field = $criteria_array['field'];
                $value = $criteria_array['value'];
                $op = $criteria_array['op'];

                $this->commandstring .= $amp. $field.'='. $value;
                $amp = '&';

                if(! empty($op)){
                $this->commandstring .=  '&'.$field.'.op='.$op;
                }
            }
            $this->commandstring .= '&-find';
        }else{
            $this->commandstring .= $amp .  '-findall';
        }

        if(!empty($sort->criteria)){
            foreach ($sort->criteria as $criteria_array){

                $field = $criteria_array['field'];
                $order = $criteria_array['order'];

                $this->commandstring .= '&'. $field;

                if(! empty($order)){
                    $this->commandstring .=  '&'.$order;
                }
            }
        }

        if(!empty($script->script_name)){

          $this->commandstring .= $script->getCommand();
        }


        $result = $this->execute();

        return $result;
    }

    function insert($view, $value_array){

        $this->setLayoutname($view);

        $command_array = $value_array;

        $command_array['-new'] = "";

        $this->setCommandarray($command_array);

        $result = $this->execute();

        return $result;

    }


    function serverScript($view, $script){

        $this->setLayoutname($view);
		$this->commandstring = '-findany';
        $this->commandstring .= $script->getCommand();
        $result = $this->execute();

        return $result;

    }

}


class FMP_where{

    public $criteria = array();

    public function __construct($field=NULL, $value=NULL, $op=NULL){
        if ( !empty($field)) {
            self::add($field, $value, $op);
        }

    }

    public function add($field, $value, $op=NULL){

        $element = count($this->criteria);
        $where_field = urlencode($field);
        $where_value = urlencode($value);
        $this->criteria[$element] = array('field'=>$where_field, 'value'=>$where_value, 'op'=>$op );

        return $this;
    }

    public function get(){
        return $this;
    }

}

class FMP_sort{

    public $criteria = array();

    public function __construct($field=NULL, $order=NULL){
        if ( !empty($field)) {
            self::add($field, $order);
        }

    }

    public function add($field, $order=NULL){

        $element = count($this->criteria);
        $sort_precedence = $element+1;

        $sort_field = '-sortfield.'.$sort_precedence.'='.urlencode($field);

        if(! empty($order)){
            $sort_order = '-sortorder.'.$sort_precedence.'='.($order == 'descend'? 'descend' : 'ascend');
        }else{
            $sort_order = '';
        }

        $this->criteria[$element] = array('field'=>$sort_field, 'order'=>$sort_order);

        return $this;
    }

    public function get(){
        return $this;
    }

}

class FMP_script{

    public $script_name = '';

    public $script_parameters = array();

    public function __construct($name){

        $this->script_name = $name;

    }

    public function addParameter($name, $value){
        $element = count($this->script_parameters);

        $parameter_name = rawurlencode($name);
        $parameter_value = rawurlencode(str_replace( '"'  ,   '\"',  $value ));

        if(! empty($parameter_name)){
            $this->script_parameters[$element] = array('name'=>$parameter_name, 'value'=>$parameter_value);
        }

        return $this;
    }

    public function get(){
        return $this;
    }


    public function getCommand(){
        $command = '&-script='. urlencode($this->script_name);

        if(! empty($this->script_parameters)){
            $command .= '&-script.param=';
            foreach($this->script_parameters as $script_parameter){

                $command .= '$'.$script_parameter['name'].'="'.$script_parameter['value'].'" ; ';
            }
        }

        return $command;
    }

}