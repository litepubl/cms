LitePublisher is content management system. LitePublisher is a simple and lightweight engine. Official website:
http://litepublisher.com/ 

Minimum System Requirements: 
- PHP 5.2
- MySQL or pdo driver

 Optional:
- apache server with rewrite module

Installation. If you have a shell access to the server you can upload files:
wget https://github.com/litepubl/cms/releases/latest
tar -xf cms.x.xx.tar.gz -p

where x.xx is current version.

Otherwise you must upload files into root folder of website. set write permissions (0777) on following folder: 
- files
- storage
- storage / backup
- storage / cache
- storage / data

In these folders you should set permissions 0666 on index.htm  files
After that  open your website in browser. Installer will ask you some questions such as E-Mail, site name and database account. 
Sometime installer can not determine language. You can change language pressing by "change language button".
Thats all. Click install to begin instalation.

To install into subfolder you should edit .htaccess file. Replace one line:
RewriteRule. / Index.php [L] 

to:
RewriteRule. / Subdir / index.php [L] 

where subdir is your subfolder.

If the installer opens in wrong encoding you can solve problem by editing . Htaccess file. Remove first char # from .htaccess
CharsetDisable On 

To increase security you can edit index.php  file. Change value in line:
  public static $ secret = '8 r7j7hbt8iik / / pt7hUy5/e/7FQvVBoh7/Zt8sCg8 + ibVBUt7rQ '; 

To manage the site go to a simple admin panel:
http://example.com/admin/ 

If you lost password you can restore your password by clicking on the link 
http://example.com/admin/passwordrecover/ 
