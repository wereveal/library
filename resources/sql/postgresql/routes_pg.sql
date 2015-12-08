--
-- Table structure for table {$dbPrefix}routes
--

CREATE TABLE {$dbPrefix}routes (
  route_id SERIAL,
  route_path character varying(128) NOT NULL,
  route_class character varying(64) NOT NULL,
  route_method character varying(64) NOT NULL,
  route_action character varying(255) NOT NULL,
  route_immutable smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (route_id)
);
CREATE UNIQUE INDEX route_path_idx ON {$dbPrefix}routes (route_path);
ALTER SEQUENCE {$dbPrefix}routes_route_id_seq RESTART WITH 31;
--
-- Dumping data for table {$dbPrefix}routes
--

INSERT INTO {$dbPrefix}routes
    (route_id, route_path, route_namespace, route_class, route_method, route_action, route_immutable)
VALUES
	(1,'/manager/','Ritc\Library\Controllers','ManagerController','render','',1),
	(2,'/manager/constants/','Ritc\Library\Controllers','ManagerController','renderConstantsAdmin','',1),
	(3,'/manager/groups/','Ritc\Library\Controllers','ManagerController','renderGroupsAdmin','',1),
	(4,'/manager/login/','Ritc\Library\Controllers','ManagerController','render','verifyLogin',1),
	(5,'/manager/logout/','Ritc\Library\Controllers','ManagerController','render','logout',1),
	(6,'/manager/pages/','Ritc\Library\Controllers','ManagerController','renderPageAdmin','',1),
	(7,'/manager/people/','Ritc\Library\Controllers','ManagerController','renderPeopleAdmin','',1),
	(8,'/manager/routes/','Ritc\Library\Controllers','ManagerController','renderRoutesAdmin','',1),
	(9,'/manager/tests/','Ritc\Library\Controllers','ManagerController','renderTestsAdmin','',1);


