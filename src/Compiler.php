<?php
namespace Tualo\Office\StatusWebsite;

use Tualo\Office\Basic\TualoApplication;
use Tualo\Office\ExtJSCompiler\ICompiler;
use Tualo\Office\ExtJSCompiler\CompilerHelper;

class Compiler implements ICompiler {
    public static function getFiles(){

        //$addRequires = TualoApplication::configuration('ext-compiler','requires',false);
        //if ($addRequires==false) throw new \Exception('requires not found in ext-compiler configuration');
        //if (strpos($addRequires,'exporter')===false) throw new \Exception('exporter requires not found in ext-compiler configuration');
        return CompilerHelper::getFiles(__DIR__,'status-website',10003);
    }
}