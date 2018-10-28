@echo off
rem Runs the PHP command line interpreter (CLI) with arguments.
rem
rem      YOU MUST EDIT THIS FILE TO ENTER THE EXACT PATH
rem      OF THE PHP EXECUTABLE INSTALLED IN YOUR SYSTEM.
rem
rem Syntax of the command:
rem
rem     php [OPTIONS] file.php
rem
rem For a complete list of the available options, type
rem
rem     php -h
rem

rem Set here the full path of the PHP CLI executable program
rem ========================================================
rem Under Windows its name is typically "php.exe", the CLI executable.
rem There are also two more versions, "php-cgi.exe" and "php-win.exe"
rem but these are not intended to work in "batch" mode on a terminal.
set PHP=D:\wamp64\bin\php\php7.1.12\php.exe

rem Set here the directory of the php.ini file
rem ==========================================
rem ATTENTION! For extra safety, the stdlib/errors.php checks this file be
rem            stdlib/php.ini or it complains otherwise. If this is not the case,
rem            change that file according to your needs here AND there.
rem
rem ATTENTION! Here you must really set the directory where the php.ini is,
rem            without the "php.ini" name itself!
rem
rem By default, here we assume to use the php.ini under stdlib/.
rem Dir. of this file, with trailing '/' added:
set __DIR__=%~dp0
set INI="%__DIR__%phplint\stdlib"
"%PHP%" -c%INI% %*
