--
-- Table structure for table {$dbPrefix}page
--

CREATE TABLE {$dbPrefix}page (
  page_id SERIAL,
  page_url character varying(255) NOT NULL DEFAULT '/',
  page_type character varying(20) NOT NULL DEFAULT 'text/html',
  page_title character varying(100) NOT NULL DEFAULT 'The Title',
  page_description character varying(150) NOT NULL DEFAULT 'The Description',
  page_base_url character varying(50) NOT NULL DEFAULT '/',
  page_lang character varying(50) NOT NULL DEFAULT 'en',
  page_charset character varying(100) NOT NULL DEFAULT 'utf-8',
  page_immutable smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (page_id),
  UNIQUE (page_url)
);

INSERT INTO {$dbPrefix}page
    (page_url, page_type, page_title, page_description, page_base_url, page_lang, page_charset, page_immutable)
VALUES
    ('/manager/','text/html','Manager','Manages People, Places and Things','/','en','utf-8',1),
    ('/manager/constants/','text/html','Manager for Constants','Manages the Constants used in app','/','en','utf-8',1),
    ('/manager/constants/verify/','text/html','Manager for Constants','Manages the Constants, verifies that the constant should be deleted.','/','en','utf-8',1),
    ('/manager/groups/','text/html','Manager for Groups','Manages the Groups','/','en','utf-8',1),
    ('/manager/groups/verify/','text/html','Manager for Groups','Manages the groups, this page verifies deletion.','/','en','utf-8',1),
    ('/manager/login/','text/html','Manager: Please Login','Login page for the manager.','/','en','utf-8',1),
    ('/manager/logout/','text/html','Manager: Please Login','Login page for the manager.','/','en','utf-8',0),
    ('/manager/pages/','text/html','Manager for Pages','Manages pages head information primarily','/','en','utf-8',1),
    ('/manager/pages/verify/','text/html','Manager for Pages','Manages pages, verifies if record should be deleted','/','en','utf-8',1),
    ('/manager/people/','text/html','Manager for People','Manages people','/','en','utf-8',1),
    ('/manager/people/verify/','text/html','Manager for People','Manages people, verifies a person should be deleted.','/','en','utf-8',1),
    ('/manager/routes/','text/html','Manager for Routes','Manages the routes','/','en','utf-8',1),
    ('/manager/routes/verify/','text/html','Manager for Routes','Manages the routes, verifies route should be deleted.','/','en','utf-8',1),
    ('/manager/tests/','text/html','Manager Tests','Runs tests for the code.','/','en','utf-8',1);

