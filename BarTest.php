<?php
require_once 'Bar.php';

class BarTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Bar
     **/
    protected $_bar;
	
	/**
	 * Enables running the test directly from this file
	 *
	 * @return	void
	 */
	public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite('BarTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }
	
	/**
     * @return void
     */
    public function setUp()
    {
        $this->_bar = new Bar();
    }
    
    /**
     * Ensure that the the security method rejects an invalid username
     *
     * @return  void
     **/
    public function testSecurityRejectsInvalidUsername()
    {
        $wssSecurity = new stdClass();
        $wssSecurity->UsernameToken = new stdClass();
        $wssSecurity->UsernameToken->Username = 'invalid';
        $wssSecurity->UsernameToken->Password = 'testpass';
        
        try {
            $this->_bar->security($wssSecurity);
            $this->fail('Expected SoapFault for invalid username.');
        } catch (SoapFault $soapFault) {
            $this->assertEquals('Invalid authentication credentials.', $soapFault->getMessage());
        }
    }
    
    /**
     * Ensure that the the security method rejects an invalid password
     *
     * @return  void
     **/
    public function testSecurityRejectsInvalidPassword()
    {
        $wssSecurity = new stdClass();
        $wssSecurity->UsernameToken = new stdClass();
        $wssSecurity->UsernameToken->Username = 'testuser';
        $wssSecurity->UsernameToken->Password = 'invalid';
        
        try {
            $this->_bar->security($wssSecurity);
            $this->fail('Expected SoapFault for invalid password.');
        } catch (SoapFault $soapFault) {
            $this->assertEquals('Invalid authentication credentials.', $soapFault->getMessage());
        }
    }
    
    /**
     * Ensure that the the security method rejects an invalid password
     *
     * @return  void
     **/
    public function testSecurityAcceptsValidCredentials()
    {
        $wssSecurity = new stdClass();
        $wssSecurity->UsernameToken = new stdClass();
        $wssSecurity->UsernameToken->Username = 'testuser';
        $wssSecurity->UsernameToken->Password = 'testpass';
        
        try {
            $this->assertNull($this->_bar->security($wssSecurity));
        } catch (SoapFault $soapFault) {
            $this->fail($soapFault->getMessage());
        }
    }
    
    /**
     * Ensure that the proper menu items are returned
     *
     * @return  void
     **/
    public function testGetMenuHasDrinks()
    {
        $menu = $this->_bar->getMenu();
        $this->assertType('stdClass', $menu);
        $this->assertTrue(isset($menu->drinks));
        $this->assertType('array', $menu->drinks);
        $this->assertFalse(empty($menu->drinks));
    }
    
    /**
     * Ensure a Shirley Temple is returned for people under 21
     *
     * @return  void
     **/
    public function testGetDrinkUnder21ReturnsShirleyTemple()
    {
        $this->assertEquals('Shirley Temple', $this->_bar->getDrink(1));
        $this->assertEquals('Shirley Temple', $this->_bar->getDrink(18));
        $this->assertEquals('Shirley Temple', $this->_bar->getDrink(20));
    }
    
    /**
     * Ensure a Beer is returned for people over 21
     *
     * @return  void
     **/
    public function testGetDrinkOver21ReturnsBeer()
    {
        $this->assertEquals('BEER!', $this->_bar->getDrink(21));
        $this->assertEquals('BEER!', $this->_bar->getDrink(31));
        $this->assertEquals('BEER!', $this->_bar->getDrink(99));
    }
    
}