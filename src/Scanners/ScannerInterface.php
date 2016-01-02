<?php
/**
 * Scabbia2 Scanners Component
 * https://github.com/eserozvataf/scabbia2
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link        https://github.com/eserozvataf/scabbia2-scanners for the canonical source repository
 * @copyright   2010-2016 Eser Ozvataf. (http://eser.ozvataf.com/)
 * @license     http://www.apache.org/licenses/LICENSE-2.0 - Apache License, Version 2.0
 */

namespace Scabbia\Scanners;

use Scabbia\Scanners\TokenStream;
use ReflectionClass;

/**
 * Default methods needed for implementation of a scanner
 *
 * @package     Scabbia\Scanners
 * @author      Eser Ozvataf <eser@ozvataf.com>
 * @since       2.0.0
 */
interface ScannerInterface
{
    /**
     * Scans a file
     *
     * @param string           $uFile             file path
     * @param string           $uFileContents     contents of file
     *
     * @return void
     */
    public function processFile($uFile, $uFileContents);

    /**
     * Scans a token stream
     *
     * @param TokenStream      $uTokenStream      extracted tokens wrapped with tokenstream
     *
     * @return void
     */
    public function processTokenStream(TokenStream $uTokenStream);

    /**
     * Processes classes using reflection
     *
     * @param string           $uClass            class name
     * @param ReflectionClass  $uReflection       reflection information for the class
     *
     * @return void
     */
    public function processClass($uClass, ReflectionClass $uReflection);

    /**
     * Finalizes the task
     *
     * @return void
     */
    public function finalize();
}
