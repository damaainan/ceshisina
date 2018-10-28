@echo off
rem Runs the PHPLint program utils/PHPLint and displays
rem the report on standard output.
rem Syntax of the command:
rem
rem     phpl [OPTIONS] file.php
rem
rem For a complete list of the available options, type
rem
rem     phpl --help
rem

rem Dir. of this file, with trailing '\' added:
set __DIR__=%~dp0

"%__DIR__%php.bat" ^
	"%__DIR__%phplint\utils\PHPLint.php" ^
	--modules-path "%__DIR__%phplint\modules" ^
	--php-version 7 ^
	--print-path relative ^
	--print-errors ^
	--print-warnings ^
	--no-print-notices ^
	--ascii-ext-check ^
	--ctrl-check ^
	--recursive ^
	--no-print-file-name ^
	--parse-phpdoc ^
	--print-context ^
	--no-print-source ^
	--print-line-numbers ^
	--report-unused ^
	%*







