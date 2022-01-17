@echo off
@setlocal
set TEST_PATH=%~dp0
if "%PHP_COMMAND%" == "" set PHP_COMMAND=php
"%PHP_COMMAND%" "%TEST_PATH%run" %*
@endlocal
