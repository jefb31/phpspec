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
namespace PHPSpec\Runner\Cli;

class Parser implements \PHPSpec\Runner\Parser
{
    /**
     * @var array
     */
    protected $_options = array(
        'noneGiven' => false,
        'c'         => false,
        'color'     => false,
        'colour'    => false,
        'h'         => false,
        'help'      => false,
        'b'         => false,
        'backtrace' => false,
        'version'   => false,
        'f'         => 'p',
        'formatter' => 'p',
        'specFile'  => '',
        'fail-fast' => false
    );
    
    /**
     * @var array
     */
     protected $_aliases = array(
        'c' => array('color', 'colour'),
        'h' => 'help',
        'b' => 'backtrace',
        'f' => 'formatter'
     );
    
    /**
     * @var array
     */
     protected $_validFormatters = array(
        'p',
        'd',
        'h',
        't',
        'progress',
        'documentation',
        'html',
        'textmate'
     );
     
     protected $_arguments;
    
    /**
     * Parses command line arguments
     *
     * @param array $arguments
     *
     * @return array|null
     */
    public function parse(array $arguments)
    {
        $this->_arguments = $arguments;
        $this->removeProgramNameFromArguments();
        if (!empty($this->_arguments)) {
            $this->extractSpecFile();
            return $this->convertArgumentIntoOptions();
        }
        throw new \PHPSpec\Runner\Cli\Error(
            'Invalid number of arguments. Type -h for help'
        );
    }
    
    /**
     * Removes phpspec script name from command line argument list
     */
    protected function removeProgramNameFromArguments()
    {
        if (is_file($this->_arguments[0])) {
            array_shift($this->_arguments);
        }
    }
    
    /**
     * Extracts spec file. If the first argument is not a - or -- option
     * it should be a spec filename
     */
    private function extractSpecFile()
    {
        if ($this->_arguments[0]{0} !== '-') {
            $this->_options['specFile'] = $this->_arguments[0];
            array_shift($this->_arguments);
        }
    }
    
    /**
     * Converts arguments into options
     */
    private function convertArgumentIntoOptions()
    {
        $arguments = new \ArrayIterator($this->getArguments());

        while($arguments->valid()) {
            $argument = $arguments->current();
            if ($this->isLongOption($argument)) {
                $this->convertLongArgumentstoOptions($arguments, $argument);
                continue;
            } elseif ($this->isShortOption($argument)) {
                $this->convertShortArgumentsToOptions($arguments, $argument);
            }
            $arguments->next();
        }
        return $this->_options;
    }
    
    /**
     * Converts long arguments, e.g. --argument value, to options
     * 
     * @param string $argument
     */
    private function convertLongArgumentstoOptions($arguments, $argument)
    {
        if ($this->isFormatterOption(substr($argument, 2))) {
            $this->checkFormatterValueIsInNextArgument($arguments);
        } else {
            $this->setOption(substr($argument, 2), true);
        }
        $arguments->next();
    }
    
    /**
     * Converts short arguments, e.g. -chv, to options
     * 
     * @param string $argument
     */
    private function convertShortArgumentsToOptions($arguments, $argument)
    {
        $options = str_split(substr($argument, 1));
        $shortOptions = new \ArrayIterator($options);
        while ($shortOptions->valid()) {
            $flag = $shortOptions->current();
            if ($this->isFormatterOption($flag)) {
                if ($this->formatterIsNextShortOption($shortOptions)) {
                    $this->setFormatterAndAdvancePointer($shortOptions, $arguments);
                } else {
                    $this->checkFormatterValueIsInNextArgument($arguments);
                }
            } else {
                $this->setOption($flag, true);
                $shortOptions->next();
            }
        }
    }
    
    public function checkFormatterValueIsInNextArgument($arguments)
    {
        $arguments->next();
        if (in_array($arguments->current(), $this->_validFormatters)) {
            $this->setFormatter($arguments->current());
        } else {
            throw new \PHPSpec\Runner\Cli\Error(
                'Invalid argument for formatter'
            );
        }
        $arguments->next();
    }
    
    protected function formatterIsNextShortOption($shortOptions)
    {
        $shortOptions->next();
        return in_array($shortOptions->current(), $this->_validFormatters);
    }
    
    protected function setFormatterAndAdvancePointer($shortOptions, $arguments)
    {
        $this->setFormatter($shortOptions->current());
        $shortOptions->next();
        $arguments->next();
    }
    
    protected function isFormatterOption($option)
    {
        return $option === 'f' || $option === 'formatter';
    }
    
    protected function isLongOption($option)
    {
        return substr($option, 0, 2) === '--';
    }
    
    protected function isShortOption($option)
    {
        return substr($option, 0, 1) === '-';
    }
    
    public function getArguments()
    {
        return $this->_arguments;
    }
    
    /**
     * Gets an option
     * 
     * @param string $name
     * @return NULL|string
     */
    public function getOption($name)
    {
        if ($this->hasOption($name)) {
            return $this->_options[$name];
        }
    }
    
    /**
     * Checks whether an option exists
     * 
     * @param string $name
     * @return bool
     */
    public function hasOption($name)
    {
        return isset($this->_options[trim($name)]);
    }
    
    /**
     * Sets an option
     * 
     * @param string $name
     * @param string $value
     */
    public function setOption($name, $value)
    {
        if (!$this->hasOption($name)) {
            throw new \PHPSpec\Runner\Cli\Error("Invalid option $name");
        }
        
        $option = $this->getOptionLongVersion($name);
        
        $setter = "set" . str_replace("-", "", ucfirst($option));
        $this->$setter($value);
    }
    
    protected function getOptionLongVersion($name)
    {
        if ($this->isOneLetterOption($name)) {
            if ($this->hasMultipleAliases($name)) {
                return $this->_aliases[trim($name[0])][0];
            }
            return $this->_aliases[trim($name[0])];
        }
        return $name;
    }
    
    public function isOneLetterOption($name)
    {
        return strlen(trim($name)) === 1;
    }
    
    public function hasMultipleAliases($name)
    {
        return is_array($this->_aliases[trim($name[0])]);
    }
    
    public function setFormatter($formatter)
    {
        $this->_options['f'] = $this->_options['formatter'] = $formatter;
    }
    
    public function setColor($color)
    {
        $this->_options['c'] = $this->_options['colour'] =
        $this->_options['color'] = $color;
    }
    
    public function setHelp($help)
    {
        $this->_options['h'] = $this->_options['help'] = $help;
    }
    
    public function setVersion($version)
    {
        $this->_options['version'] = $version;
    }
    
    public function setAutospec($autospec)
    {
        $this->_options['a'] = $this->_options['autospec'] = $autospec;
    }
    
    public function setBacktrace($backtrace)
    {
        $this->_options['b'] = $this->_options['backtrace'] = $backtrace;
    }
    
    public function setFailfast($failfast)
    {
        $this->_options['failfast'] = $failfast;
    }
}