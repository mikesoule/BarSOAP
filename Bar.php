<?php
class Bar
{
    /**
     * Authentication status
     *
     * @var boolean
     **/
    protected $_isAuthenticated = false;
    
    /**
     * Authentication method for handling the Security SOAP header (per WS-Security)
     *
     * @param   object $auth
     * @return  void
     */
    public function auth(stdClass $auth)
    {
        if ($auth->Username == 'testuser' && $auth->Password == 'testpass') {
            $this->_isAuthenticated = true;
        }
    }
    
    /**
     * Get the bar menu
     *
     * @return  stdClass
     **/
    public function getMenu()
    {
        $this->_checkAuth();
        
        $menu = new stdClass();
        $menu->Drinks = array('BEER!', 'Shirley Temple');
        return $menu;
    }
    
    /**
     * Returns the appropriate drink.
     *
     * @param   integer $age
     * @return  string
     **/
    public function getDrink($age)
    {
        $this->_checkAuth();
        
        return $age >= 21 ? 'BEER!' : 'Shirley Temple';
    }
    
    /**
     * undocumented function
     *
     * @return  void
     **/
    public function isAuthenticated()
    {
        return $this->_isAuthenticated;
    }
    
    /**
     * Secure the service from unauthorized requests
     *
     * @throws SoapFault
     * @return  void
     **/
    private function _checkAuth()
    {
        if ($this->_isAuthenticated == false) {
            throw new SoapFault('Sender', 'Invalid authentication credentials.');
        }
    }
    
}
