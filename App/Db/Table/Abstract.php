<?php

/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';

/**
 * Class for SQL table interface.
 *
 * @category   App
 * @package    App_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2009 Domas Greicius
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class App_Db_Table_Abstract extends Zend_Db_Table_Abstract
{

     /**
     * Gets related records data, according to $this->_referenceMap
     *
     * @param array $works
     * @return array 
     */
    public function getParents($works,$options=null){
        $parentData = array();
        
/*        cachiname duomenis is kitu lenteliu */

            foreach ($this->_referenceMap as $relationName=>$reference) {  
                $class = new $reference['refTableClass']();
                $info = $class->info();
                $distinct = $this->getDistinct($works, $reference['columns']);
                $distinctData = $this->getDistinctData($distinct, $info['name'], $reference['refColumns'],$options);
                $parentData[$relationName] = $distinctData;
            }
            $results =  $this->getDistinctArrayMerged($works, $parentData);
        return $results;
    }
    
    /**
     * Get distinct items in a specific column from an array
     *
     * @param Array $items
     * @param string $column
     * @return array - distinct items array
     */
    public function getDistinct($items, $column){
        $distinctItems = array();
        foreach ($items as $item) {
            $distinctItems[$item[$column]] = $item[$column];
        }
        return $distinctItems;
    }
    
    /**
     * Enter description here...
     *
     * @param unknown_type $distinctUsers
     * @param unknown_type $tableName
     * @param unknown_type $columnName
     * @return unknown
     */
    public function getDistinctData($distinctItems, $tableName, $columnName,$options = null){
        /**
         * Make a MySQL IN statement
         */        
        $inTxt = '';
        if(is_array($distinctItems) && count($distinctItems) > 0) {
            $sep = '';
            $inTxt = 'IN(';
            foreach ($distinctItems as $item_id) 
            {
                if($item_id !=null)
                {
                    $inTxt .= $sep . $item_id;
                    $sep = ',';
                }
            }
            $inTxt .= ')';
        }
        /**
         * Get related records with IN statement
         */       
        $order = isset($options['order'])?$options['order']:'id ASC';
        $itemsList = array();  
        try {
            $itemsList = $this->_db->fetchAll('select * from ' . $tableName . 
                                                      ' where ' . $columnName . ' ' . $inTxt . 
                                                      " order by $order") ;
        }
        catch (Zend_Db_Statement_Mysqli_Exception $e) {
            $itemsList = array(); 
        }
        /**
         * Prepare items array for easier handling
         * We make an array like this: array('{item_id}'=>{item_data});
         */   
        $items = array();     
        foreach ($itemsList as $item) {
            $items[$item['id']] = $item;
        }
        unset($itemsList);
        
        return $items;
        
    }
    
    /**
     * Prepare final array of objects
     * Object is the model that is associated with other models (items)
     * Array structure: 
     * array(
     *          0=>array(
     *                      'Object'=>array({object data}),
     *                      'Item1'=>array({Item1 data}),
     *                      'Item2'=>array({Item2 data}),
     *                  );
     * )
     *
     * @param array $works
     * @param array $data
     */
    public function getDistinctArrayMerged($objects, $parents){
        $objectsItems = array();
        $objectName = array_pop(explode('_', get_class($this)));
        $primaryKeyColumn = $this->_primary[1];
        
        if($primaryKeyColumn == null)
            $primaryKeyColumn = 'id';
        foreach ($objects as $object) {
            $objectsItems[$object[$primaryKeyColumn]][$objectName] = $object;
            foreach ($parents as $model=>$data) {
                if(isset($data[$object[$this->_referenceMap[$model]['columns']]]))
                   $objectsItems[$object[$primaryKeyColumn]][$model] = $data[$object[$this->_referenceMap[$model]['columns']]];
            }
        }
        return $objectsItems;
        
    }
    /**
    * Return array for combobox
    * 
    * @param $firstEmpty - add empty line on top list
    * @param $title - add text to first line
    * @param $target - column name for display
    */
    public function getXModelPairs($firstEmpty=false,$title=false,$target='name',$options=null)
    {
        $result = array();
        if(!$title)
            $title = '-------';
        $select = $this->select();
        
        if(!empty($options))
            $this->_setSelectWhere($select,$options);
        if($firstEmpty)
        {   
            $result[0] = $title;
            $select->from($this->_name,array('id',$target));
            $results = $this->_db->fetchPairs($select);
            $result += $results;
        }
        else
        {
            $select->from($this->_name,array('id',$target));
                                     
            $result = $this->_db->fetchPairs($select);
        }
        return $result;
    }
    
    /**    
    * Find x column value from Model where y column value = y_value value
    * Example: Get tag id (x) by Id (y) where Id (y) = y_value
    * 
    * @param $x 
    * @param $y
    * @param $y_value
    * @return array
    */
    public function findXByY($x,$y,$y_value,$options=null)
    {                               
        $y_value = $y_value;                                                
        $result = array();  
        $select = $this->select();
        $select->where($y.'= ?',$y_value);
        isset($options['order']) ? $this->_setSelectOrder($select,$options['order']):'';
        isset($options['limit']) ? $select->limit($options['limit']):'';
        isset($options['where']) ? $this->_setSelectWhere($select,$options['where']) : '';
        
        // sitas paliktas kad senesnes versijos puslapis veiktu
        if(isset($options['created']))
            $select->order($options['created']);
            
        if($x == 'all')
        {
            $select->from($this->_name);
            $result = $this->_db->fetchAll($select);
        }
        else
        {
            $select->from($this->_name,$x);
            $result = $this->_db->fetchRow($select);      
        }                         
        return $result;
    }
    
    protected function _setSelectWhere($s,$o,$sign = '=')
    {
        foreach($o as $k => $v)
            $s->where($k . $sign . ' ?',$v);
    }
    
    protected function _setSelectWhereMix($s,$o)
    {
        foreach($o as $v)
            $s->where($v);
    }    
    /**
    * select'ui prideda orderinimo atributus
    * 
    * @param object $s = $this->select 
    * @param array  $o = array('DESC'=>'created')
    */
    protected function _setSelectOrder($s,$o)
    {
        foreach($o as $k=>$v)
            $s->order($k.' '.$v);
    }
    /**
    * Find all models data
    * 
    * @return array $data
    */
    public function findAll($options=null)
    {
        $select = $this->select();
        
        if(isset($options['order']))
            $this->_setSelectOrder($select,$options['order']);
            
        $select->from($this->_name);    
        return $this->_db->fetchAll($select);
    }
    
    public function getAllDataSelect($options = array(), $order = null)
    {
        $orderTxt = 'modified DESC';
        // Gaunam table'o description'a kad galetume tikrinti ar yra atitinkami stulpeliai
        $tableDescription = $this->getAdapter()->describeTable($this->_name);
        
        if(isset($order) && !empty($order)){
            
            if($order == 'latest' && isset($tableDescription['latest'])){
                $orderTxt = 'created DESC';
            }
            if($order == 'popular' && isset($tableDescription['download_count'])){
                $orderTxt = 'download_count DESC';
            }
            if($order == 'highest' && isset($tableDescription['rate_count'])){
                $orderTxt = 'rate_count DESC';
            }
            if($order == 'id' && isset($tableDescription['id'])){
                $orderTxt = 'id ASC';
            }
            if($order == 'promote' && isset($tableDescription['position'])){
                $orderTxt = 'position DESC';
            }
            
        }
        
        $select = $this->select();
        if(isset($options['order']))
            $this->_setSelectOrder($select,$options['order']);

        if(empty($options))
            return $select->from($this->_name, '*')->order($orderTxt);
        else
        {
            $select->from($this->_name, '*')->order($orderTxt);
            
            if(isset($options['orMixCondition']))
                $select->orWhere($options['orMixCondition']); 
                
            if(isset($options['inText']))
                $select->where($options['inText']['target'] . ' ' . $options['inText']['value']);
                
            foreach($options as $value)
            {
                if(isset($value['condition']))
                    if(isset($tableDescription[$value['condition']])){
                        $operator = '=';
                        if(isset($value['operator'])) $operator = $value['operator'];
                        $select->where($value['condition'].' '.$operator.' ?',$value['value']);
                    }
                if(isset($value['orcondition']))
                    if(isset($tableDescription[$value['orcondition']])){
                        $operator = '=';
                        if(isset($value['operator'])) $operator = $value['operator'];

                        $select->orWhere($value['orcondition'].' '.$operator.' ?',$value['value']);
                    }
            }
            
            return $select;
        }
    }
    
    /**
    * Save a new entry
    *
    * @param  array $data
    * @return int|string
    */
    public function save(array $data)
    {
        $data = $this->_prepare($data);
        $this->_manageCache();
        return $this->insert($data);
    }
    
    /**
     * Updates existing rows and deletes cached data
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int          The number of rows updated.
     */      
    public function update(array $data,$where)
    {
        $this->_manageCache();
        return parent::update($data,$where);
    }
    
    /**
     * Deletes existing rows and cache'd data
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     */    
    public function delete($where)
    {
        $this->_manageCache();
        return parent::delete($where);
    }
    
    private function _manageCache()
    {
        $this->cleanCache($this->_cacheName);
        $this->cleanCache($this->getEnabledCacheName());
        $this->cleanCache($this->getEnabledPairsCacheName());         
    }
    
    protected function _prepare(array $data){
        $fields = $this->info(Zend_Db_Table_Abstract::COLS);
        foreach ($data as $field => $value) {
            if (!in_array($field, $fields)) {
                unset($data[$field]);
            }
        }
        return $data;    
    }
    
    public function getEnabledPairsCacheName()
    {
        if(isset($this->_enabledPairsCacheName))
            return $this->_enabledPairsCacheName;
        return null;
    }
    
    public function getEnabledCacheName()
    {
        if(isset($this->_enabledCacheName))
            return $this->_enabledCacheName;
        return null;
    }    
   
       
    /**
    * Update multiple rows    
    * 
    * @param $array - field must be updated and value. ETC.: array(indexed=>1)
    * @param $values - go to IN 
    * @return
    */
    protected function multipleUpdate($tableName,$columnName,$values)
    {
         $inTxt = '';
         $sep = '';
         $inTxt = 'IN(';
         foreach ($values as $value)          
            if($value !=null)
            {
                $inTxt .= $sep . $value;
                $sep = ',';
            }
         $inTxt .= ')';
         try {
            $this->getAdapter()->query('update ' . $tableName . ' set indexed=1 where ' . $columnName . ' ' . $inTxt);
        }
         catch (Zend_Db_Statement_Mysqli_Exception $e) {  
        $itemsList = array(); 
        }
    }

    /**
    * 
    *
    * @param $array - field must be updated and value. ETC.: array(indexed=>1)
    * @param $values - go to IN
    * @return
    */


    /**
    * Update multiple rows
    *
    * @param array position - masyvas id pagal eiliskuma
    * @param mixed $targets - lenteles lauku pavadinimu masyvas
    * @return Zend_Db_Statement_Interface
    */
    public function multipleMixUpdate($position,$targets)
    {
        $sql = "insert into {$this->_name} (";
        $i = 1;
        $sep = ',';
        $numItem = count($targets);
        foreach($targets as $value)
        {
            $numItem != $i ? $sql .= 'id, '.$value.$sep : $sql .= 'id, '.$value;
            $i++;
        }
        $sql .= ") values";
        unset($value);

        $i = 0;
        $numItem = count($position);
        foreach($position as $value)
        {
            $sql .= "(";
            $numVal = count($value);
            $sql .= strip_tags($value).','. ++$i;
            $sql .= ')';
            --$numItem != 0 ? $sql .= $sep : $sql .= '';
        }
       
        $sql .= ' ON DUPLICATE KEY UPDATE position =values(position)';
        return $this->getAdapter()->query($sql);
    }
    /**
    * Reikia tik paomtizimsuoti kad bet kokiai lenteliai tiktu, db gali iterpti tik i many to many tarpine lentele
    * 
    * @param mixed $data - iterpimiamu duomenu masyvas
    * @param mixed $targets - lenteles lauku pavadinimu masyvas
    * @return Zend_Db_Statement_Interface
    */
    public function multipleSave($data,$targets)
    {
        $sql = "insert into {$this->_name} (";
        $i = 1;
        $sep = ',';
        $numItem = count($targets);
        foreach($targets as $value)
        {
            $numItem != $i ? $sql .= $value.$sep : $sql .= $value;
            $i++;
        }
        $sql .= ") values";
        unset($value);
        
        $numItem = count($data);
        foreach($data as $value)
        {    
            $sql .= "(";
            $numVal = count($value);
            foreach($value as $v)
            {
                $sql .= is_numeric($v) ? $v : '\''.$v.'\'';
                --$numVal != 0 ? $sql .= $sep : $sql .= ')';
            }
            --$numItem != 0 ? $sql .= $sep : $sql .= '';
        }                
        return $this->getAdapter()->query($sql);
    }
    
     public function inText($data,$target=null)
    {
        $sep = '';
        $inTxt = 'IN (';
        foreach ($data as $value) 
        {
            if($value !=null)
            {
                if($target!=null)
                    $inTxt .= $sep . mysql_real_escape_string($value["$target"]);
                else $inTxt .= $sep . '\''.$value.'\'';
                $sep = ',';
            }
        }
        $inTxt .= ' )'; 
        return $inTxt;  
    }
    
    public function getAvailableProperties($name)
    {
        $oCache = Zend_Registry::get('cache');
        $oCacheID = 'available_'.$name;
        if(!$oCache->test($oCacheID))
        {
            $this->devFirebugMessage('Cachina_'.$name);
            $results = $this->findXByY('all','disabled',0);
            $oCache->save($results,$oCacheID);
        }else
        {
            $this->devFirebugMessage('from Cache_'.$name);
            $results = $oCache->load($oCacheID);
        }
        return $results;
    }
    
    /**
    * Istrina visus yrasus susijusius su duotu targetu
    * 
    * @param value of target
    * @param target default is id, but can be customized
    */
    public function deleteAll($tvalue,$target='id')
    {
        $select = $this->select();
        $select->from($this->_name,$target);
        $select->where("$target =".$tvalue);
        $o = $this->fetchAll($select)->toArray();

        if(!empty($o))
            $this->delete("$target =".$o[0][$target]);
    }
    
    /**
    * @desc istrina cache'a pagala uzcache'inta name'a
    * @param string | array
    */
    
    public function cleanCache($names)
    {
         $oCache = Zend_Registry::get('cache');
         $this->devFirebugMessage('clean cache ');
         if(!is_null($names))
             if(!is_array($names) )
             {
                if($oCache->test($names))
                {
                    $oCache->remove($names);
                    $this->devFirebugMessage('Cache: clean cache '.$names);
                    
                }else
                     $this->devFirebugMessage('Cache: nothing to clean '.$names);
             }
             else
                foreach($names as $name)
                {
                    if($oCache->test($name))
                    {
                        $oCache->remove($name);
                        $this->devFirebugMessage('Cache: clean cache '.$name);      
                    }
                    else
                        $this->devFirebugMessage('Cache: nothing to clean '.$name);
                }
    }
    
    /**
    * This function deletes a directory with all of it's content.
    * @param string - dir path
    * @param boolen - Second parameter is boolean to instruct the function 
    *                 if it should remove the directory or only the content
    */
    public function rmdir_r ($dir, $DeleteMe = TRUE )
    {
        if ( ! $dh = @opendir ( $dir ) ) return;
        while ( false !== ( $obj = readdir ( $dh ) ) )
        {
            if ( $obj == '.' || $obj == '..') continue;
            if ( ! @unlink ( $dir . DS . $obj ) ) $this->rmdir_r ( $dir . DS . $obj, true );
        }
        
        closedir ( $dh );
        if ( $DeleteMe )
        {
            @rmdir ( $dir );
        }
    }
    
    public function recurse_copy($src,$dst) 
    {
        $moved = false;
        $dir = opendir($src); 
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . DS . $file) ) { 
                    $this->recurse_copy($src . DS . $file,$dst . DS . $file); 
                } 
                else { 
                    $moved = copy($src . DS . $file,$dst . DS . $file); 
                } 
            } 
        } 
        closedir($dir); 
        return $moved;
    }     
    
    public function getLastId()
    {
        return $this->_db->lastInsertId();
    }
    
    /**
    * @desc Grazina uzcache'intus duomenis arba duomenis uzcachin'a ir tada grazina
    */
    public function getAllPairs($target = 'name',$option = null)
    {
        if(is_null($option))
            $opt = array(false,false,$target);
        else
            $opt = $option;
        return $this->_getCachedPairsData($this->_cacheName,$opt);
//        return $this->getXModelPairs(false,false,'city_name');
    }
    
    /**
    * @desc Grazina uzcache'intus duomenis arba duomenis uzcachin'a ir tada grazina dropdown'ui
    */
    public function getAllEnabledPairs($target = 'name', $options = null)
    {
        if($options == null)
            $options = array('disabled'=>0);
        $opt = array(false,false,$target,'opt'=>$options);
        return $this->_getCachedPairsData($this->_enabledPairsCacheName,$opt);
    }      
    
    /**
    * @desc grazina modelio varda is didziosios raides atskirta nuo viso namespace'o
    */
    public function getModel()
    {
        return $this->_model;
    }

    public function getDBName()
    {
        return $this->_name;
    }
    public function getControllerName()
    {
        return $this->_controller;
    }
    
    public function getDesiredLengthDescription($desc, $desiredLen = 100){
    
        $mainText   = explode(" ", substr($desc, 0, $desiredLen + 25));
        $numChr     = 0;
        $string     = "";
        foreach($mainText as $word)
        {
            $numChr += strlen($word);
            
            if($numChr <= $desiredLen)
                $string .= $word . " ";
            else
                break;
        }
        return $string;
        
    }
    
    /**
    * @desc Cache'ina arba grazina uz'cache'intus duomenis
    * @param $oCacheID  - name of cached data (required)
    * @param $options   - parameter to findXByY function array('all,'id','value')
    * @param $chaceName - get from register cahce method name 
    */
    protected function _getCachedData($oCacheID,$options, $cacheName = 'cache')
    {
        $oCache = Zend_Registry::get($cacheName);
        if(!$oCache->test($oCacheID))
        {  $this->devFirebugMessage('cachina '.$oCacheID);
            if(empty($options['opt']))
                $data = $this->findXByY($options['0'],$options['1'],$options['2']);
            else                
                $data = $this->findXByY($options['0'],$options['1'],$options['2'],$options['opt']);
            $oCache->save($data,$oCacheID);
        }
        else
        {
            $this->devFirebugMessage('loadina is chacheo '.$oCacheID);
            $data = $oCache->load($oCacheID);
        }
        return $data;
    }

    /**
    * @desc Cache'ina arba grazina uz'cache'intus duomenis
    * @param $oCacheID  - name of cached data (required)
    * @param $options   - parameter to findXByY function array('all,'id','value')
    * @param $chaceName - get from register cahce method name 
    */
    protected function _getCachedPairsData($oCacheID,$options, $cacheName = 'cache')
    {
        $oCache = Zend_Registry::get($cacheName);
        if(!$oCache->test($oCacheID))
        {  $this->devFirebugMessage('cachina pairs'.$oCacheID);
            if(empty($options['opt']))
                $data = $this->getXModelPairs($options['0'],$options['1'],$options['2']);
            else                
                $data = $this->getXModelPairs($options['0'],$options['1'],$options['2'],$options['opt']);
            $oCache->save($data,$oCacheID);
        }
        else
        {
            $this->devFirebugMessage('loadina is chacheo pairs '.$oCacheID);
            $data = $oCache->load($oCacheID);
        }
        return $data;
    }    
    
    
    protected function _getCacheName()
    {
        return $this->_cacheName;
    }
    
    public function devFirebugMessage($message)
    {
        if(APPLICATION_ENVIRONMENT == 'development')
                App_Debug::fire($message);
    }
}
