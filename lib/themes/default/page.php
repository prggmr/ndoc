<!doctype html>
<html>
    <head>
        <title>
            <?=ucfirst(str_replace('_', ' ', $__current_page))?> &mdash; <?=$doc_title?>
        </title>
    </head>
</head>
<body>
    <?=$__content?>
    <hr />
    Documentation Version <?=$doc_version?> <br />
    Last Updated <?=$last_updated?> <br />
    <?=$copyright?>
    <hr />
    Documentation created using <a href="http://ndoc.prggmr.com">ndoc</a>
</body>
</html>