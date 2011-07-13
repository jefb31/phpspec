<?php
/**
 * PHPSpec
 *
 * LICENSE
 *
 * This file is subject to the GNU Lesser General Public License Version 3
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/lgpl-3.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phpspec.net so we can send you a copy immediately.
 *
 * @category  PHPSpec
 * @package   PHPSpec
 * @copyright Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright Copyright (c) 2010-2011 Pádraic Brady, Travis Swicegood,
 *                                    Marcello Duarte
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
namespace PHPSpec\Specification;

use \PHPSpec\Runner\Reporter,
    \PHPSpec\Specification\Interceptor\InterceptorFactory,
    \PHPSpec\Specification\Result\Pending,
    \PHPSpec\Specification\Result\DeliberateFailure;

class ExampleGroup
{
    
    public function beforeAll()
    {
        
    }
    
    public function before()
    {
        
    }
    
    public function afterAll()
    {
        
    }
    
    public function after()
    {
        
    }
    
    public function spec()
    {
        return call_user_func_array(
            array(
                '\PHPSpec\Specification\Interceptor\InterceptorFactory',
                'create'),
            func_get_args()
        );
    }
    
    public function pending($message = 'No reason given')
    {
        throw new Pending($message);
    }
    
    public function fail($message = '')
    {
        $message = empty($message) ? '' : PHP_EOL . '       ' . $message;
        throw new DeliberateFailure(
            'RuntimeError:' . $message
        );
    }
    
    /**
     * Wrapper for {@link \PHPSpec\Mocks\Mock} factory
     * 
     * @param string $class
     * @param array  $stubs
     * @return object
     */
    public function double($class = 'stdClass', $stubs = array(), $arguments = array())
    {
        if (is_dir(__DIR__ . '/../Mocks')) {
            $double = new \PHPSpec\Mocks\Mock();
            $double = $double->create($class, array(), $arguments);
            if (!empty($stubs)) {
                foreach ($stubs as $stub => $value) {
                    $double->stub($stub)->andReturn($value);
                }
            }
            return $double;
        }
        throw new \PHPSpec\Exception('PHPSpec_Mocks is not installed');
    }

    /**
     * Wrapper for {@link \PHPSpec\Mocks\Mock} factory
     * 
     * @param string $class
     * @param array  $stubs
     * @return object
     */
    public function mock($class = 'stdClass', $stubs = array(), $arguments = array())
    {
        return $this->double($class, $stubs, $arguments);
    }

    /**
     * Wrapper for {@link \PHPSpec\Mocks\Mock} factory
     * 
     * @param string $class
     * @param array  $stubs
     * @return object
     */
    public function stub($class = 'stdClass', $stubs = array(), $arguments = array())
    {
        return $this->double($class, $stubs, $arguments);
    }
}