
## Load and view MARC21-Files retreived from ##
## Deutsche National Bibliothek DNB ##


The Deutsche National Bibliothek [DNB](https://www.dnb.de/DE/Home/home_node.html) offers downloads of metadata of new
publications. Metadata is delivered as MARC21 files. This service is free of charge.
More details can be found here: [Ongoing update of metadata](https://www.dnb.de/EN/Professionell/Metadatendienste/Datenbezug/Laufend/laufend_node.html) 


This documentation provides instructions for installing and using the marc21DB data  
loader, including setting up the database, configuring the PHP files, and running  
the script to load MARC21 data files.  

## **DEMO**

A fully working demo is located here:

[https://vmd55223.contaboserver.net/marc21DB/](https://vmd55223.contaboserver.net/marc21DB/)



## Dependencies

- bulma css framework
- fontawesome6

Refered to in the sources of **classes/page.php**  

<pre>
&lt;link rel="stylesheet" href="/bulma/css/bulma.min.css">   
&ltlink rel="stylesheet" href="/font-awesome-6/css/all.min.css">  
</pre>

## Prerequisit

- PHP 8.x
- mySQL/mariaDB
- bulma
- fontawesome

## Installation

### Step 1 

Download the source tree into your webspace.  
Name the top level directory **marc21DB**

Load/execute the given **marc21.sql**.  
This will create a database 

- **marc21**
- fill table **ddc** with the code and description
- create constraints for cascading delete   


### Step 2

In the directory **marc21DB/include** edit the file **connect.inc.php** in order to set
the neccessary _dbname_ , _user_ and _password_ for your database.   

File **adressPort.inc.php** keeps adress of a websocket server to allow the backend to send feedback to the client
If you are not using my [phpWebsocketServer](https://github.com/napengam/phpWebSocketServer) do not set **$Address** in there, 

In the include Directory run   

``
php ddc2DB.php
``

This will fill table ddc with code and description for language 'de' and 'en'


### Step 3

In a shell/command line, step into director **marc21DB/classe-get21**. All files to download, read and insert data from 
marc21 files are located here. As a first test run 

``
php getFiles.php -y 24 -w 01 -s A
``

This will download/load the file **A2401utf8.mrc** from the DNB.

Parameters are:

- -y is the year as two digits (default is current year)
- -w is the calendar week (default is current calendar week)
- -s is the series you want to load

Upon succes the script will continously print out the internal id of 
the title loaded and the ddc associated with this title.

While loading from the file the table search is filled with the title author and publisher  
in order to allow for fulltext search within these fields  

### Step4 

Now you can call your URL adressing **marc21DB**

The GUI should come up and present you with the titles from the latest marc21 file and a facette with
all the ddc found.   


## Full text search

Column what in table search holds text of marc21 fields for title,author,publisher. 
For this column a full text index is maintained allowing for fast full text search with 
titel or author od publisher. 

To delete and recreate the table and index call `` maintain/buildFullText.php ``