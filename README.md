# README #

This README would normally document whatever steps are necessary to get your application up and running.

## The Reveal IT Consulting PHP Library ##

- Quick summary -- This provides a library of classes used to build an app.
- Version -- 5.5.0

## How do I get set up? ##

- Normally this is setup in the ritc framework.
- There are setup resources in the resources dir

## Contributing ##

- Try to follow PHP-FIG coding standards (PSR-1 and PSR-2) with following exceptions allowed (frequently used)
    - PSR-1 4.2 avoids recommendations. 
        - For my projects I use $under_score property names.
        - I have used $camelCase property names but like the readability of $under_score names.
        - I use $o_ to start vars that are objects. Helps at a glance to know what it is.
        - I use $a_ to start vars that are arrays. Helps at a glance to know what it is.
    - PSR-2
        - 2.3 Lines Quite frankly, PSR-2.3 is confusing. It says...
            - MUST NOT be a hard limit on line length
            - Soft Limit MUST be 120 characters
            - yet they also specifiy Lines SHOULD NOT be longer than 80 characters.
            - HUH??? So, what I recommend
                - No hard limit on line length - I couldn't care how long the lines are.
                - Soft Limit is 120 characters, I usually set my page guide to 120.
                - I aim at 80 characters but really, who uses a terminal that narrow? Mine often is 190+ characters.
        - 2.5 Keywords and True/False/Null
            - Opinion is they are being lazy with true, false, and null. 
            - I use lower case now but don't really care unless I want to emphasize a value.
            - They are constants and can be all upper case, e.g. TRUE, FALSE, NULL - see PSR-1 4.1
        - 5 Control Structures
            - I prefer to have elseif, else, while, and catch statements on their own line
            - e.g. 
```
#!php

             if ($this) {
                 // do something
             }
             elseif ($that) {
                 // do that something
             }
             else {
                 // do the default thing
             }
```
- But some inconsistency may creep in over time. The goal is to remain as consistent as possible for readability and understandability over time without drowning out individuality.

## Who do I talk to? ##

- William E Reveal <bill@revealitconsulting.com>
