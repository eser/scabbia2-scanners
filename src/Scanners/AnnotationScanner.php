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

use Scabbia\Scanners\ScannerInterface;
use Scabbia\Scanners\TokenStream;
use LogicException;
use ReflectionClass;

/**
 * canner
 *
 * @package     Scabbia\Scanners
 * @author      Eser Ozvataf <eser@ozvataf.com>
 * @since       2.0.0
 */
class AnnotationScanner implements ScannerInterface
{
    /** @type array       $result      result of scanning task */
    public $result = [];
    /** @type array       $ignoreList  annotations to be ignored */
    public $ignoreList = [
        "link",
        "copyright",
        "license",
        "package",
        "author",
        "since",
        "type",
        "param",
        "return",
        "throws",
        "todo",
        "see",
        "ignore"
    ];


    /**
     * Scans a file
     *
     * @param string           $uFile             file path
     * @param string           $uFileContents     contents of file
     *
     * @return void
     */
    public function processFile($uFile, $uFileContents)
    {
    }

    /**
     * Scans a token stream
     *
     * @param TokenStream      $uTokenStream      extracted tokens wrapped with tokenstream
     *
     * @return void
     */
    public function processTokenStream(TokenStream $uTokenStream)
    {
    }

    /**
     * Processes classes using reflection
     *
     * @param string           $uClass            class name
     * @param ReflectionClass  $uReflection       reflection information for the class
     *
     * @return void
     */
    public function processClass($uClass, ReflectionClass $uReflection)
    {
        $tClassAnnotations = [];

        $tDocComment = $uReflection->getDocComment();
        if (strlen($tDocComment) > 0) {
            $tParsedAnnotations = $this->parseAnnotations($tDocComment);
            if (count($tParsedAnnotations) > 0) {
                $tClassAnnotations["class"] = ["self" => $tParsedAnnotations];
            }
        }

        // methods
        foreach ($uReflection->getMethods() as $tMethodReflection) {
            // TODO check the correctness of logic
            if ($tMethodReflection->class !== $uClass) {
                continue;
            }

            $tDocComment = $tMethodReflection->getDocComment();
            if (strlen($tDocComment) > 0) {
                $tParsedAnnotations = $this->parseAnnotations($tDocComment);

                if (count($tParsedAnnotations) === 0) {
                    // nothing
                } elseif ($tMethodReflection->isStatic()) {
                    if (!isset($tClassAnnotations["staticMethods"])) {
                        $tClassAnnotations["staticMethods"] = [];
                    }

                    $tClassAnnotations["staticMethods"][$tMethodReflection->name] = $tParsedAnnotations;
                } else {
                    if (!isset($tClassAnnotations["methods"])) {
                        $tClassAnnotations["methods"] = [];
                    }

                    $tClassAnnotations["methods"][$tMethodReflection->name] = $tParsedAnnotations;
                }
            }
        }

        // properties
        foreach ($uReflection->getProperties() as $tPropertyReflection) {
            // TODO check the correctness of logic
            if ($tPropertyReflection->class !== $uClass) {
                continue;
            }

            $tDocComment = $tPropertyReflection->getDocComment();
            if (strlen($tDocComment) > 0) {
                $tParsedAnnotations = $this->parseAnnotations($tDocComment);

                if (count($tParsedAnnotations) === 0) {
                    // nothing
                } elseif ($tPropertyReflection->isStatic()) {
                    if (!isset($tClassAnnotations["staticProperties"])) {
                        $tClassAnnotations["staticProperties"] = [];
                    }

                    $tClassAnnotations["staticProperties"][$tPropertyReflection->name] = $tParsedAnnotations;
                } else {
                    if (!isset($tClassAnnotations["properties"])) {
                        $tClassAnnotations["properties"] = [];
                    }

                    $tClassAnnotations["properties"][$tPropertyReflection->name] = $tParsedAnnotations;
                }
            }
        }

        if (count($tClassAnnotations) > 0) {
            $this->result[$uClass] = $tClassAnnotations;
        } else {
            $this->result[$uClass] = null;
        }
    }

    /**
     * Finalizes the task
     *
     * @return void
     */
    public function finalize()
    {
    }

    /**
     * Parses the docblock and returns annotations in an array
     *
     * @param string $uDocComment docblock which contains annotations
     *
     * @return array set of annotations
     */
    protected function parseAnnotations($uDocComment)
    {
        preg_match_all(
            "/\\*[\\t| ]\\@([^\\n|\\t| ]+)(?:[\\t| ]([^\\n]+))*/",
            $uDocComment,
            $tDocCommentLines,
            PREG_SET_ORDER
        );

        $tParsedAnnotations = [];

        foreach ($tDocCommentLines as $tDocCommentLine) {
            if (in_array($tDocCommentLine[1], $this->ignoreList)) {
                continue;
            }

            if (!isset($tParsedAnnotations[$tDocCommentLine[1]])) {
                $tParsedAnnotations[$tDocCommentLine[1]] = [];
            }

            if (isset($tDocCommentLine[2])) {
                $tParsedAnnotations[$tDocCommentLine[1]][] = trim($tDocCommentLine[2]);
            }
        }

        return $tParsedAnnotations;
    }
}
