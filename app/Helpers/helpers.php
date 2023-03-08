<?php
// check if responder helper function doesn't exists
if (!function_exists('responder')) {
    // responder function
    function responder()
    {
        return new \App\Helpers\Responder\ResponseBuilder();
    }
}
