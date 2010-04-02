<?php
class Bar
{
    
    /**
     * Authentication method for handling the Security SOAP header (per WS-Security)
     *
     * @return  void
     */
    public function security(stdClass $wssSecurity)
    {
        if ($wssSecurity->UsernameToken->Username != 'testuser' || $wssSecurity->UsernameToken->Password != 'testpass') {
            throw new SoapFault('Sender', 'Invalid authentication credentials.');
        }
    }
    
    /**
     * Get the bar menu
     *
     * @return  stdClass
     **/
    public function getMenu()
    {
        $menu = new stdClass();
        $menu->drinks = array('BEER!', 'Shirley Temple');
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
        return $age >= 21 ? 'BEER!' : 'Shirley Temple';
    }
    
}
