<?php 
abstract class Filter_Filter 
{
	abstract static function getInstance();
//	protected static $_name;
	public function getName()
	{
		return $this->_name;
	}
	public function getFilterObj()
	{
		return $this;
	}
	public function getFilter()
	{
		return $this->_filters;
	}
	public function removeFilter($params)
	{
        $sname = $this->_searchByName;
        if(!empty($this->_filters))
        {
		    if(!isset($parms['title']) && in_array($params['title'],$this->_filters[$sname]))
			    unset($this->_filters[$sname][$params['id']]);
        }
        else
        {
            if(!isset($parms['title']))
                unset($this->_filters[$sname][$params['id']]);            
        }
	}
}
?>
