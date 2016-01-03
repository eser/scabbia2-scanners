# Scabbia2 Scanners Component

[This component](https://github.com/eserozvataf/scabbia2-scanners) scans the source directories and compiles some information. It is basically designed for extracting annotations from docblocks but functionality can be extended by implementing `Scabbia\Scanners\ScannerInterface`.

[![Build Status](https://travis-ci.org/eserozvataf/scabbia2-scanners.png?branch=master)](https://travis-ci.org/eserozvataf/scabbia2-scanners)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/eserozvataf/scabbia2-scanners/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/eserozvataf/scabbia2-scanners/?branch=master)
[![Total Downloads](https://poser.pugx.org/eserozvataf/scabbia2-scanners/downloads.png)](https://packagist.org/packages/eserozvataf/scabbia2-scanners)
[![Latest Stable Version](https://poser.pugx.org/eserozvataf/scabbia2-scanners/v/stable)](https://packagist.org/packages/eserozvataf/scabbia2-scanners)
[![Latest Unstable Version](https://poser.pugx.org/eserozvataf/scabbia2-scanners/v/unstable)](https://packagist.org/packages/eserozvataf/scabbia2-scanners)
[![Documentation Status](https://readthedocs.org/projects/scabbia2-documentation/badge/?version=latest)](https://readthedocs.org/projects/scabbia2-documentation)

## Usage

### Extracting Annotations from Source Folder

```php
use Scabbia\Scanners\Scanners;
use Scabbia\Scanners\AnnotationScanner;

$annotationScanner = new AnnotationScanner();

$scanners = new Scanners();
$scanners->register($annotationScanner);
$scanners->processFolder('src/');

var_dump($annotationScanner->result);
```

### Custom Scanner

```php
use Scabbia\Scanners\Scanners;
use Scabbia\Scanners\ScannerInterface;
use Scabbia\Scanners\TokenStream;
use ReflectionClass;

$customScanner = new class () implements ScannerInterface {
    public function processFile($file, $fileContents) {
        echo 'processing file ', $file;
    }

    public function processTokenStream(TokenStream $tokenStream) {
    }

    public function processClass($class, ReflectionClass $reflection) {
        echo 'processing class ', $class;
    }

    public function finalize() {
        echo 'done.';
    }
};

$scanners = new Scanners();
$scanners->register($customScanner);
$scanners->processFolder('src/');
```

## Links
- [List of All Scabbia2 Components](https://github.com/eserozvataf/scabbia2)
- [Documentation](https://readthedocs.org/projects/scabbia2-documentation)
- [Twitter](https://twitter.com/eserozvataf)
- [Contributor List](contributors.md)
- [License Information](LICENSE)


## Contributing
It is publicly open for any contribution. Bugfixes, new features and extra modules are welcome. All contributions should be filed on the [eserozvataf/scabbia2-scanners](https://github.com/eserozvataf/scabbia2-scanners) repository.

* To contribute to code: Fork the repo, push your changes to your fork, and submit a pull request.
* To report a bug: If something does not work, please report it using GitHub issues.
* To support: [![Donate](https://img.shields.io/gratipay/eserozvataf.svg)](https://gratipay.com/eserozvataf/)
