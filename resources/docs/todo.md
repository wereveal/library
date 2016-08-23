# TODO

## Random Db Thoughts

* Ritc\Library\Traits\DbUtilityTraits
    - genericUpdate() needs to be modified to handle multiple record updates.
    - genericDeleteMultiple([1,2,3]) needs to be created
    - could use a method to determine the db table name (base name) from the model name.
        * e.g., SectionModel.php, class SectionModel would then create 'section' for the base name of the
          database table. 
        * This would be used in the setProperties method instead of having to pass in the table name. 
        * Forces the model to be precise in the name, a table name would force a complete refactoring, 
          maybe a good thing, maybe a bad. 
        * Consider the renaming of the word item to entity (or some other term)
          - serious refactoring there
          - could theoretically be confusing with the Entities classes

* On multiple anything, should transactions be invoked? Or a switch be added to allow for that?

* nav_ng_map table should have an automagical primary key

* There are several tables (mostly map) which have field names like fs_sec_id to help keep straight
  the various field names apart which would otherwise be named the same in different tables.
  This may be too verbose since in most cases, we are either not using addtional tables
  or we are using the table_name.field_name syntax so there is no confusion. Oh yeah, the confusion
  comes from joins, which field in which table in results. Hmm, would have to do an AS to distinguish.

* Want to be able to distinguish immediately between app tables and default library tables.
    - A new constant for LIB_DB_PREFIX could be created and the Library code reflect it. 
    - This would be an alternative to renaming all the tables to be specific to the library
    - Would eliminate using the db_config.php::prefix variable however. 
    - Add an additional db_config::lib_prefix variable to that file?
    - Problem: DbUtilitiesTraits wouldn't know that the model is for a library table. Extend it for Library?
    - Problem: DbTraits wouldn't know either.
    - Solution?:
        1. Some common traits between DbTraits and DbUtilityTraits need to be moved to DbCommonTraits
        2. Create a DbLibTraits file? or would the revised DbUtilityTraits be sufficient?
        3. Add a lib_prefix variable to the db_config.php file
        4. Add a method to the revised DbCommonTraits to set the lib_prefix.
        5. Use the new lib_prefix in all the Library Models.
  
 
## Noticed this needs done

* Ritc\Library\Helper\ViewHelper could use some tweeking.
    - ViewHelper::messageProperties comments need cleaned up
    - I feel that I am doing a lot of redundant stuff, couldn't the ViewHelper::errorMessage etc
      methods also call the ViewHelper::messageProperties method and get everything formatted in one
      step where now, one has to call them individually. 

* Ritc\Library\Services\Elog
    - several properties in Elog need SETter/GETter methods
        * $elog_file
        * $json_file
        * $json_log_used
        * $error_email_address
        * $debug_text
    - most likely need to then add a method in the LogitTraits class to call the SETters too.
    - wonder if the log file path should be changed from a CONSTANT to a variable or allow for change
      to be made on the fly.
      
## Random Other Thoughts

* See if you can write/find a web front-end to phpUnit for testing so testing can be
  done at either CLI or web and get rid of your own Testing class.
        
