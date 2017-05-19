### SQLite3 类 
SQLite3::busyTimeout — Sets the busy connection handler  
SQLite3::changes — Returns the number of database rows that were changed (or inserted or deleted) by the most recent SQL statement  
SQLite3::close — Closes the database connection  
SQLite3::__construct — Instantiates an SQLite3 object and opens an SQLite 3 database  
SQLite3::createAggregate — Registers a PHP function for use as an SQL aggregate function  
SQLite3::createCollation — Registers a PHP function for use as an SQL collating function  
SQLite3::createFunction — Registers a PHP function for use as an SQL scalar function  
SQLite3::enableExceptions — Enable throwing exceptions  
SQLite3::escapeString — Returns a string that has been properly escaped  
SQLite3::exec — Executes a result-less query against a given database  
SQLite3::lastErrorCode — Returns the numeric result code of the most recent failed SQLite request  
SQLite3::lastErrorMsg — Returns English text describing the most recent failed SQLite request  
SQLite3::lastInsertRowID — Returns the row ID of the most recent INSERT into the database  
SQLite3::loadExtension — Attempts to load an SQLite extension library  
SQLite3::open — Opens an SQLite database  
SQLite3::openBlob — Opens a stream resource to read a BLOB  
SQLite3::prepare — Prepares an SQL statement for execution  
SQLite3::query — Executes an SQL query  
SQLite3::querySingle — Executes a query and returns a single result  
SQLite3::version — Returns the SQLite3 library version as a string constant and as a number  


### SQLite3Stmt 类 
SQLite3Stmt::bindParam — Binds a parameter to a statement variable  
SQLite3Stmt::bindValue — Binds the value of a parameter to a statement variable  
SQLite3Stmt::clear — Clears all current bound parameters  
SQLite3Stmt::close — Closes the prepared statement  
SQLite3Stmt::execute — Executes a prepared statement and returns a result set object  
SQLite3Stmt::paramCount — Returns the number of parameters within the prepared statement  
SQLite3Stmt::readOnly() — Returns whether a statement is definitely read only  
SQLite3Stmt::reset — Resets the prepared statement  


### SQLite3Result 类 
SQLite3Result::columnName — Returns the name of the nth column  
SQLite3Result::columnType — Returns the type of the nth column  
SQLite3Result::fetchArray — Fetches a result row as an associative or numerically indexed array or both  
SQLite3Result::finalize — Closes the result set  
SQLite3Result::numColumns — Returns the number of columns in the result set  
SQLite3Result::reset — Resets the result set back to the first row  
