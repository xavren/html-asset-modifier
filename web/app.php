<?php
require __DIR__.'/../vendor/autoload.php';

use Asset\HtmlModifier;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

if ($request->isMethod('POST')) {
    $modifier = new HtmlModifier($request->request->get('path'), $request->request->get('pattern'));
    $html = $modifier->modify($request->request->get('html'));

    $textMode = $request->request->get('text') == 1;
    if ($textMode) {
        echo $html;
    } else {
        printf('<html><body><pre>%s</pre></body></html>', htmlspecialchars($html));
    }

    return;
}

?>
<html>
<head>
    <style>
        textarea, input[type="text"]
        {
            border:1px solid #999999;
            width:100%;
            margin:5px 0;
            padding:3px;
        }

        textarea {
            min-height: 50%;
        }
    </style>
</head>
<body>
<form method="post" target="_blank">
    Path
    <input type="text" name="path">
    Pattern
    <input type="text" name="pattern" value='{{ asset("%s") }}'>
    HTML
    <textarea name="html"></textarea>
    <input type="submit" value="Submit">
</form>
</body>
</html>
