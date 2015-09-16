<?php
/**
 * Scabbia2 Scanners Component
 * http://www.scabbiafw.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link        https://github.com/scabbiafw/scabbia2-scanners for the canonical source repository
 * @copyright   2010-2015 Scabbia Framework Organization. (http://www.scabbiafw.com/)
 * @license     http://www.apache.org/licenses/LICENSE-2.0 - Apache License, Version 2.0
 */

namespace Scabbia\Scanners;

use Scabbia\Helpers\FileSystem;
// use Scabbia\Scanners\ScannerInterface;
use LogicException;
use ReflectionClass;

/**
 * Scanners registry
 *
 * @package     Scabbia\Scanners
 * @author      Eser Ozvataf <eser@ozvataf.com>
 * @since       2.0.0
 */
class Scanners
{
    /** @type array      scanner classes */
    public $scanners = [];


    /**
     * Registers a scanner
     *
     * @param array|string   $uScanners    scanner instances
     *
     * @return void
     */
    public function register($uScanners)
    {
        foreach ((array)$uScanners as $tScanner) {
            $this->scanners[] = $tScanner;
        }
    }

    /**
     * Scans all files in folders
     *
     * @param array|string  $uFolders          folders
     *
     * @return void
     */
    public function processFolder($uFolders)
    {
        foreach ((array)$uFolders as $tFolder) {
            FileSystem::getFilesWalk(
                $tFolder,
                "*.php",
                true,
                [$this, "processFile"]
            );
        }
    }

    /**
     * Scans a file
     *
     * @param string      $uFile             file path
     *
     * @return void
     */
    public function processFile($uFile)
    {
        $tFileContents = FileSystem::read($uFile);

        foreach ($this->scanners as $tScanner) {
            $tScanner->processFile($uFile, $tFileContents);
        }

        $tTokenStream = TokenStream::fromString($tFileContents);
        $this->processTokenStream($tTokenStream);
    }

    /**
     * Scans a token stream
     *
     * @param TokenStream $uTokenStream      extracted tokens wrapped with tokenstream
     *
     * @return void
     */
    public function processTokenStream(TokenStream $uTokenStream)
    {
        foreach ($this->scanners as $tScanner) {
            $tScanner->processTokenStream($uTokenStream);
        }

        $tBuffer = "";

        $tUses = [];
        $tLastNamespace = null;
        $tLastClass = null;
        $tLastClassDerivedFrom = null;
        $tExpectation = 0; // 1=namespace, 2=class

        foreach ($uTokenStream as $tToken) {
            if ($tToken[0] === T_WHITESPACE) {
                continue;
            }

            if ($tExpectation === 0) {
                if ($tToken[0] === T_NAMESPACE) {
                    $tBuffer = "";
                    $tExpectation = 1;
                    continue;
                }

                if ($tToken[0] === T_CLASS) {
                    $tExpectation = 2;
                    continue;
                }

                if ($tToken[0] === T_USE) {
                    $tBuffer = "";
                    $tExpectation = 5;
                    continue;
                }
            } elseif ($tExpectation === 1) {
                if ($tToken[0] === T_STRING || $tToken[0] === T_NS_SEPARATOR) {
                    $tBuffer .= $tToken[1];
                } else {
                    $tLastNamespace = $tBuffer;
                    $tExpectation = 0;
                }
            } elseif ($tExpectation === 2) {
                $tLastClass = "{$tLastNamespace}\\{$tToken[1]}";
                $tExpectation = 3;
            } elseif ($tExpectation === 3) {
                if ($tToken[0] === T_EXTENDS) {
                    $tBuffer = "";
                    $tExpectation = 4;
                    continue;
                }

                $tSkip = false;
                if ($tLastClassDerivedFrom !== null && !class_exists($tLastClassDerivedFrom)) {
                    $tSkip = true;
                    throw new LogicException(sprintf(
                        "\"%s\" derived from \"%s\", but it could not be found.\n",
                        $tLastClass,
                        $tLastClassDerivedFrom
                    ));
                }

                if (!$tSkip && !isset($this->result[$tLastClass])) {
                    $this->processClass($tLastClass);
                }

                $tExpectation = 0;
            } elseif ($tExpectation === 4) {
                if ($tToken[0] === T_STRING || $tToken[0] === T_NS_SEPARATOR) {
                    $tBuffer .= $tToken[1];
                } else {
                    $tFound = false;

                    foreach ($tUses as $tUse) {
                        $tLength = strlen($tBuffer);
                        if (strlen($tUse) >= $tLength && substr($tUse, -$tLength) === $tBuffer) {
                            $tLastClassDerivedFrom = $tUse;
                            $tFound = true;
                            break;
                        }
                    }

                    if (!$tFound) {
                        if (strpos($tBuffer, "\\") !== false) {
                            $tLastClassDerivedFrom = $tBuffer;
                        } else {
                            $tLastClassDerivedFrom = "{$tLastNamespace}\\{$tBuffer}";
                        }
                    }

                    $tExpectation = 3;
                }
            } elseif ($tExpectation === 5) {
                if ($tToken[0] === T_STRING || $tToken[0] === T_NS_SEPARATOR) {
                    $tBuffer .= $tToken[1];
                } else {
                    $tUses[] = $tBuffer;
                    $tExpectation = 0;
                }
            }
        }
    }

    /**
     * Processes classes using reflection
     *
     * @param string      $uClass            class name
     *
     * @return void
     */
    public function processClass($uClass)
    {
        $tReflection = new ReflectionClass($uClass);

        foreach ($this->scanners as $tScanner) {
            $tScanner->processClass($uClass, $tReflection);
        }
    }
}
