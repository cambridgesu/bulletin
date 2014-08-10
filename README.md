Bulletin system
===============

This is a PHP application which implements a bulletin system, enabling users to submit entries, and for administrators to assemble the bulletin from those entries and send it out.

Screenshot
----------

![Screenshot](screenshot.png)


Usage
-----

1. Clone the repository.
2. Download the library dependencies and ensure they are in your PHP include_path.
3. Download and install the famfamfam icon set in /images/icons/
4. Add the Apache directives in httpd.conf (and restart the webserver) as per the example given in .httpd.conf-extract.txt; the example assumes mod_macro but this can be easily removed.
5. Create a copy of the index.html.example file as index.html in the URL directory where the application will run from, and fill in the parameters.
6. Access the page in a browser at a URL which is served by the webserver.
7. You will be prompted to quote a database superuser password, so that the database structure can be installed by the application.


Dependencies
------------

* [application.php application support library](http://download.geog.cam.ac.uk/projects/application/)
* [database.php database wrapper library](http://download.geog.cam.ac.uk/projects/database/)
* [frontControllerApplication.php front controller application implementation library](http://download.geog.cam.ac.uk/projects/frontcontrollerapplication/)
* [ultimateForm.php form library](http://download.geog.cam.ac.uk/projects/ultimateform/)
* [FamFamFam Silk Icons set](http://www.famfamfam.com/lab/icons/silk/)


Author
------

Martin Lucas-Smith, CUSU, 2010-14.


License
-------

GPL2.

