<?php
// error_handler.php

function handleError($errno, $errstr, $errfile, $errline) {
    // Log the error
    error_log("Error [$errno]: $errstr in $errfile on line $errline");

    // Show friendly message
    include 'error_page.html';
    exit();
}

set_error_handler("handleError");

function handleException($exception) {
    // Log the exception
    error_log("Uncaught Exception: " . $exception->getMessage());

    // Show friendly message
    include 'error_page.html';
    exit();
}

set_exception_handler('handleException');
