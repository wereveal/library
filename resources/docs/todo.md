# TODO

## Random Db Thoughts

* Ritc\Library\Services\DbModel
    - need to change the selectDbTables method to return a cleaner array
    - need to add a method to verify a table exists
    - need to add some generic methods to do basic data definition actions (CREATE, ALTER, etc)
        * May need to be its own class
        * Or maybe just a trait

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

## Sessions

* Need to think about making Sessions persistent over load balancers. Save session stuff to database?

## Noticed this needs done

* Need to rewrite most classes to implement Exceptions/SPL Exceptions.
    - Create new Exception classes that extend the various default Exceptions/SPL Exceptions
    - Start with the database classes
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

* Ritc\Library\Helper\Arrays
    - The following need to be able to handle array of assoc arrays (and rename param on many from a_pairs)
        - Arrays::createRequiredPairs()
        - Arrays::hasRequiredKeys()
        - Arrays::hasBlankValues()
        - Arrays::removeSlashes()
        - Arrays::removeUndesiredPairs()
        - Arrays::stripTags()
        - Arrays::stripUnsafePhp()
    - Arrays::cleanArrayValues()
        - maybe change html_entities to filter_var($value, FILTER_SANITIZE_STRING)
        - if change to filter_var also change ent_flag to array(FILTER_FLAG_NO_ENCODE_QUOTES) or default to ''. See php documentation.
        - Also, maybe figure out a way to specify what type of filter to use per value
          * Based on key name? email or e-mail = FILTER_SANITIZE_EMAIL etc would force better form design
          * Based on a different parameters - change ent_flag to sanitize_filter or something like that?

## Random Other Thoughts

* See if you can write/find a web front-end to phpUnit for testing so testing can be
  done at either CLI or web and get rid of your own Testing class.

## Future Additions

* URL aliases
  * CREATE TABLE `lib_aliases` (
      `a_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `a_url_id` int(11) unsigned NOT NULL,
      `a_alias` varchar(150) NOT NULL DEFAULT '',
      PRIMARY KEY (`a_id`),
      KEY `a_url_id` (`a_url_id`),
      CONSTRAINT `lib_aliases_ibfk_1` FOREIGN KEY (`a_url_id`) REFERENCES `lib_urls` (`url_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  * Add controller, view, models for manager
  * Update Router and/or RoutesHelper to handle aliases