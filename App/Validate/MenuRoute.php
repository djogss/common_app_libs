<?php

require_once 'Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class App_Validate_MenuRoute extends Zend_Validate_Abstract
{
    const BAD_ROUTE = 'badRoute';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::BAD_ROUTE => "'%value%' route doesnt exist."
    );

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value exist in DB.
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
                   if($value != null)
        {
            $navigation_data = new Zend_Config_Xml(CONFIGS_PATH . 'navigation.xml', APPLICATION_ENVIRONMENT);
            $routes = $navigation_data->routes->toArray();
            if(!isset($routes[$value]))
            {
                $this->_error(self::BAD_ROUTE,$value);
                return false;
            }
        }

        return true;
    }

}
