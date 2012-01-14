<!doctype html>
<html>
    <head>
        <title>
            {{ current_page | capitalize }} &mdash; {{ settings.doc_title }}
        </title>
        <link rel="stylesheet" tyle="text/css" href="<?=$ndoc_static('static/css/default.css', '{{ page_path }}')?>">
    </head>
</head>
<body>
    {% block content %}
    {% endblock %}
    <hr />
    {% if next_chapter_link %}
        <a href="<?=$ndoc_link('{{ next_chapter_link }}', '{{ page_path }}')?>">Next Chapter</a>
    {% endif %}
    {% if previous_chapter_link %}
        <a href="<?=$ndoc_link('{{ previous_chapter_link }}', '{{ page_path }}')?>">Previous Chapter</a>
    {% endif %}
    Documentation Version {{ settings.doc_version }} <br />
    Last Updated {{ settings.last_updated }} <br />
    {{ settings.copyright }}
    <hr />
    Documentation created using <a href="http://ndoc.prggmr.com">ndoc</a>
</body>
</html>