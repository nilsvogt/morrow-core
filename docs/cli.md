CLI usage
=============================

The framework is usable by the command line interface. This is very useful for cron jobs or your own cool scripts.

Format
------

You just have to follow this format in your shell:

~~~{.bash}
[php] [index.php] [path] [parameters]
~~~


Pattern | Description
--------| ------------
`php` | The path to your php interpreter.
`index.php` | The path to the index.php in `app/public/`.
`path` | The page path you want to call. If you have a page you would usually call by `http://domain.com/home/` you would pass `home/` or `home` here.
`parameters` | Pass the parameters you want to use via the Input class in query string format.



Example
-------

If your current working directory is `.../app/public/`, an example call to the homepage could be:
~~~{.bash}
php index.php home foo=bar&foo2=bar2
~~~

That would call the homepage with the both $_GET parameters `foo` and `foo2` and returns its html source code.
