<?php
ini_set("soap.wsdl_cache_enabled", 0);

require_once dirname(__FILE__) . '/Bar.php';

class BarTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Bar
     **/
    protected $_bar;
    
    /**
     * undocumented class variable
     *
     * @var SoapClient
     **/
    protected $_soapClient;
	
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
        
        // create mock SoapClient with trace enabled for retrieving raw requests/responses
        $this->_soapClient = $this->getMock('SoapClient', 
                                            array('_doRequest'), 
                                            array(
                                                dirname(__FILE__) . '/bar.wsdl.xml', 
                                                array('trace' => 1)
                                            ));
        
        // prevent SoapClient from generating http requests
        $this->_setSoapClientResponse(null);
    }
    
    /**
     * @return  void
     **/
    public function tearDown()
    {
        unset($this->_bar);
        unset($this->_soapClient);
    }
    
    /**
     * Ensure that the the auth method rejects an invalid username
     *
     * @return  void
     **/
    public function testSecurityRejectsInvalidUsername()
    {
        $auth = new stdClass();
        $auth->Username = 'invalid';
        $auth->Password = 'testpass';
        
        $this->_bar->auth($auth);
        $this->assertFalse($this->_bar->isAuthenticated());
    }
    
    /**
     * Ensure that the the auth method rejects an invalid password
     *
     * @return  void
     **/
    public function testSecurityRejectsInvalidPassword()
    {
        $auth = new stdClass();
        $auth->Username = 'testuser';
        $auth->Password = 'invalid';
        
        $this->_bar->auth($auth);
        $this->assertFalse($this->_bar->isAuthenticated());
    }
    
    /**
     * Ensure that the the auth method rejects an invalid password
     *
     * @return  void
     **/
    public function testSecurityAcceptsValidCredentials()
    {
        $auth = new stdClass();
        $auth->Username = 'testuser';
        $auth->Password = 'testpass';
        
        $this->_bar->auth($auth);
        $this->assertTrue($this->_bar->isAuthenticated());
    }
    
    /**
     * Ensure that the proper menu items are returned
     *
     * @return  void
     **/
    public function testGetMenuHasDrinks()
    {
        $this->_authBar();
        
        $menu = $this->_bar->getMenu();
        $this->assertType('stdClass', $menu);
        $this->assertTrue(isset($menu->Drinks));
        $this->assertType('array', $menu->Drinks);
        $this->assertFalse(empty($menu->Drinks));
    }
    
    /**
     * Ensure a Shirley Temple is returned for people under 21
     *
     * @return  void
     **/
    public function testGetDrinkUnder21ReturnsShirleyTemple()
    {
        $this->_authBar();
        
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
        $this->_authBar();
        
        $this->assertEquals('BEER!', $this->_bar->getDrink(21));
        $this->assertEquals('BEER!', $this->_bar->getDrink(31));
        $this->assertEquals('BEER!', $this->_bar->getDrink(99));
    }
    
    /**
     * Ensure that a SOAP client can find the exposed methods via the WSDL
     *
     * @return  void
     **/
    public function testMethodsExist()
    {
        $expectedMethods = array(
            'GetMenuRS GetMenu()',
            'string GetDrink(integer $Age)',
        );
        
        $methods = $this->_soapClient->__getFunctions();
        
        $this->assertEquals(count($expectedMethods), count($methods), var_export($methods, true));
        
        foreach ($expectedMethods as $method) {
            $this->assertContains($method, $methods, var_export($methods, true));
        }
    }
    
    /**
     * Ensures that GetMenu cannot be called without authentication
     *
     * @return  void
     **/
    public function testGetMenuIsSecure()
    {
        try {
            $this->_soapClient->GetMenu();
            $this->fail('Expected SoapFault for inauthentication.');
        } catch (SoapFault $fault) {
            $this->assertEquals('Invalid authentication credentials.', $fault->getMessage());
        }
    }
    
    /**
     * Ensures that GetDrink cannot be called without authentication
     *
     * @return  void
     **/
    public function testGetDrinkIsSecure()
    {
        try {
            $this->_soapClient->GetDrink();
            $this->fail('Expected SoapFault for inauthentication.');
        } catch (SoapFault $fault) {
            $this->assertEquals('Invalid authentication credentials.', $fault->getMessage());
        }
    }
    
    /**
     * Ensure GetMenu works via SOAP
     *
     * @return  void
     **/
    public function testSoapGetMenu()
    {
        // set security headers to avoid SoapFault
        $this->_setClientSecurityHeaders();
        
        // generate a SOAP request
        $this->_soapClient->GetMenu();
        
        // $request is used in index.php
        $request = $this->_soapClient->__getLastRequest();
        
        // use output buffering to trap SoapServer response
        ob_start();
        @include dirname(__FILE__) . '/index.php';
        $response = ob_get_clean();
        
        // set SoapClient response
        $this->_setSoapClientResponse($response);
        
        // generate PHP object from the response
        $menu = $this->_soapClient->GetMenu();
        
        $this->assertType('stdClass', $menu);
        $this->assertTrue(isset($menu->Drinks));
        $this->assertType('array', $menu->Drinks);
        $this->assertFalse(empty($menu->Drinks));
    }
    
    /**
     * Ensure GetDrink works via SOAP
     *
     * @return  void
     **/
    public function testSoapGetDrinkUnder21()
    {
        // set security headers to avoid SoapFault
        $this->_setClientSecurityHeaders();
        
        // generate a SOAP request
        $this->_soapClient->GetDrink(20);
        
        // $request is used in index.php
        $request = $this->_soapClient->__getLastRequest();
        
        // use output buffering to trap SoapServer response
        ob_start();
        @include dirname(__FILE__) . '/index.php';
        $response = ob_get_clean();
        
        // set SoapClient response
        $this->_setSoapClientResponse($response);
        
        // generate PHP object from the response
        $drink = $this->_soapClient->GetDrink(20);
        
        $this->assertEquals('Shirley Temple', $drink);
    }
    
    /**
     * Ensure GetDrink works via SOAP
     *
     * @return  void
     **/
    public function testSoapGetDrinkOver21()
    {
        // set security headers to avoid SoapFault
        $this->_setClientSecurityHeaders();
        
        // generate a SOAP request
        $this->_soapClient->GetDrink(21);
        
        // $request is used in index.php
        $request = $this->_soapClient->__getLastRequest();
        
        // use output buffering to trap SoapServer response
        ob_start();
        @include dirname(__FILE__) . '/index.php';
        $response = ob_get_clean();
        
        // set SoapClient response
        $this->_setSoapClientResponse($response);
        
        // generate PHP object from the response
        $drink = $this->_soapClient->GetDrink(21);
        
        $this->assertEquals('BEER!', $drink);
    }
    
    /**
     * Authorize Bar in order to test other public methods
     *
     * @return  void
     **/
    protected function _authBar()
    {
        $auth = new stdClass();
        $auth->Username = 'testuser';
        $auth->Password = 'testpass';
        
        $this->_bar->auth($auth);
    }
    
    /**
     * Sets the WS-Security headers
     *
     * @return  void
     **/
    protected function _setClientSecurityHeaders()
    {
        $auth = new stdClass();
        $auth->Username = 'testuser';
        $auth->Password = 'testpass';
        $this->_soapClient->__setSoapHeaders(new SoapHeader('http://barsoap.dev', 'Auth', $auth, true));
    }
    
    /**
     * Set the raw response for the SoapClient
     *
     * @return  void
     **/
    protected function _setSoapClientResponse($response = null)
    {
        $this->_soapClient->expects($this->any())
                          ->method('_doRequest')
                          ->will($this->returnValue($response));
    }
    
}