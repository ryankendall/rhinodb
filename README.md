rhinodb
=======

ORM Framework for PHP
---------------------

rhinodb does what PHP should have done from the start. 
It creates a PHP object from a database row.

Usage:
------

Simply extend your class with`DBRecord` to gain the features of rhinodb.

eg.
  
    <?php
    class User extends DBRecord {}

But what use is it??
--------------------
Grab a single result from the database by creating an object:

    <?php
    $user = new User(array('username' => 'rhinobean'));
    echo $user->username;
    echo $user->id;
  
    -- MAKES --
    
    SELECT * FROM users WHERE username = 'rhinobean' LIMIT 1

Find a selection of possible results:

    <?php
    $banned = User::findByStatus(2);
    foreach ($banned as $user){ ... }
    
    -- MAKES --
    
    SELECT * FROM users WHERE status = 2


Yes, you did read that correctly! you can use 'ActiveRecord' style find_by_x_or_y methods :D

    <?php
    class User extends DBRecord {
      public static function joinedToday() {
        return self::findByDateJoined(date("Y-m-d H:i:s"));
      }
    }

Assumptions
-----------
rhinodb makes some assumptions about your DB setup.
  - Your database tables are the same as the classname (but pluralised) you setup
    - This can be overridden: declare a static class property $TBL with the name of your table
  - Your column names include underscores i.e. date_joined

TODO
----
  - Give option to specify your DB naming conventions so magic meth takes this into account
  
