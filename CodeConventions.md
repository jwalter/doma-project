# Code conventions #

The following code conventions are used in this project. Or at least we are trying to use them...

## Valid XHTML ##
  * The output should be valid XHTML.

## Text editing ##
  * Indent code with two spaces. Do not use tabs.
  * Files should be saved in utf-8 encoding.

## PHP ##
  * File names in the root directory are in lowercase\_separated\_by\_underscores.php.
  * File names for classes are in UpperCamelCase.php.
  * Variable names are in $lowerCamelCase.
  * Function names are in lowerCamelCase.
  * Class names are in UpperCamelCase.
  * Class member and function names are right now in UpperCamelCase, but should probably be transformed to lowerCamelCase to follow general PHP coding style.
  * The php code opening tag is `<?php`. Do not use `<?` since PHP can be configured not to work with it.
  * The php code closing tag is `?>`.
  * Braces are placed on a new line:
```
if($someBooleanExpression)
{
  doSomething();
}
else
{
  doSomethingElse();
}
```
  * A MVC-like pattern is used for the output pages. Each output page foo.php has a companion page foo.controller.php. The separation of concerns philosophy is implemented such that foo.php contains markup (and maybe some tiny logic, but not much!), and foo.controller.php contains logic.
    * The foo.php page starts by `require_once("foo.controller.php");`, which defines a class FooController with a function Execute.
```
<?php
  include_once(dirname(__FILE__) ."/foo.controller.php");
  
  $controller = new FooController();
  $vd = $controller->Execute();
?>
```
    * FooController::Execute extracts all data that is needed to display the page and returns it as an associative array.
    * foo.php uses that array to output data: `<?php print $vd["SomeDynamicDataFromTheController"]; ?>`

## Javascript ##
  * The [unobtrusive javascript pattern](http://en.wikipedia.org/wiki/Unobtrusive_JavaScript) is used.
  * The [jQuery](http://www.jquery.com) library is used.
  * Try to implement complex javascript functionality as jQuery plugins.

## CSS ##
todo

## File structure ##
  * All browsable files (those who generates output) are located in the root directory.
  * PHP library files specific for the DOMA project are located in /include.
  * PHP class files specific for the DOMA project are located in /entities.
  * PHP static utility class files specific for the DOMA project are located in /util.
  * External PHP libraries are located in /lib.
  * Images and graphics are located in /gfx.
  * Language files are located in /languages.
  * The style sheet is named /style.css. Maybe it will be split onto several files later on.
  * Javascript files are located in /js.