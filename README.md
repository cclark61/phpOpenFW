-----------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------
## phpOpenFW
-----------------------------------------------------------------------------------------------------------
-----------------------------------------------------------------------------------------------------------
phpOpenFW is an open source MVC PHP web development framework released under the GNU Public License (GPL) version 2.

-----------------------------------------------------------------------------------------------------------
## Author
-----------------------------------------------------------------------------------------------------------
Christian J. Clark

-----------------------------------------------------------------------------------------------------------
## Website
-----------------------------------------------------------------------------------------------------------
http://www.emonlade.net/phpopenfw/

-----------------------------------------------------------------------------------------------------------
## License
-----------------------------------------------------------------------------------------------------------
GNU Public License (GPL) version 2 ( http://www.gnu.org/licenses/gpl-2.0.txt )

-----------------------------------------------------------------------------------------------------------
## Version
-----------------------------------------------------------------------------------------------------------
1.3.0

-----------------------------------------------------------------------------------------------------------
## Requirements
-----------------------------------------------------------------------------------------------------------
phpOpenFW requires PHP >= 5.3, libxslt, libxml, php-xsl, and php-xml.

-----------------------------------------------------------------------------------------------------------
## Support
-----------------------------------------------------------------------------------------------------------
Contact support@emonlade.net for comments questions, or concerns.

-----------------------------------------------------------------------------------------------------------
## Features
-----------------------------------------------------------------------------------------------------------
phpOpenFW has an abundance of features that facilitate the development of powerful, flexible web sites and web applications. Below is an outline of the features offered by phpOpenFW.

#### Framework Facilities

* Form Engine
* Database Abstraction Class
* Active Record Class
* XML Element Class (abstract)
* Recordset List to Table Class
* Generic XHTML Table Class
* Plugin Facility

#### Application Facilities

* Built-in Authentication services
* Module list / Navigation Facility

#### Plugins

* XML Transformation (using XSL)
* Quick Database Actions
* Date/Time Functions
* Code Benchmark

-----------------------------------------------------------------------------------------------------------
## Apache Mod_Rewrite Rules
-----------------------------------------------------------------------------------------------------------
When using the nav_xml_format of "rewrite", you need to have to following apache mod_rewrite rules 
in place for the application navigation to work correctly. You can tweak the rules to suit you application, 
but there needs to be a catch-all rule that forward all pages through the applications main index.php 
script. Also, the pass-through for the CSS, images, and Javascript is important as well.

#### Example:

RewriteEngine On
RewriteRule ^([^/\.]+).html$ index.php?page=$1 [L]
RewriteRule ^(themes|css|img|javascript) - [L]
RewriteRule  .*favicon\.ico$ - [L]
RewriteRule ^.*$ index.php [L,qsa]

**If you are using Virtual Document Roots with Apache your rules will most likely need to look something like this:**

RewriteEngine On
RewriteBase /
RewriteRule ^([^/\.]+).html$ index.php?page=$1 [L]
RewriteRule ^(themes|css|img|javascript) - [L]
RewriteRule Ê.*favicon\.ico$ - [L]
RewriteRule ^.*$ index.php [L,qsa]

