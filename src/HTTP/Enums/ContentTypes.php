<?php

namespace Konarsky\HTTP\Enums;

enum ContentTypes: string
{
    case TEXT_PLAIN = 'text/plain';
    case TEXT_HTML = 'text/html';
    case APPLICATION_JSON = 'application/json';
    case APPLICATION_XML = 'application/xml';
    case APPLICATION_JAVASCRIPT = 'application/javascript';
    case TEXT_CSS = 'text/css';
    case APPLICATION_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';
    case MULTIPART_FORM_DATA = 'multipart/form-data';
}
