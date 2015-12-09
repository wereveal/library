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
    (route_id, route_path, route_class, route_method, route_action, route_immutable)
VALUES
    ( 1, '/manager/','ManagerController','render','',1),
    ( 2, '/manager/login/','ManagerController','render','verifyLogin',1),
    ( 3, '/manager/routes/','ManagerController','renderRoutesAdmin','',1),
    ( 4, '/manager/constants/','ManagerController','renderConstantsAdmin','',1),
    ( 5, '/manager/people/','ManagerController','renderPeopleAdmin','',1),
    ( 6, '/manager/groups/','ManagerController','renderGroupsAdmin','',1),
    ( 7, '/manager/roles/','ManagerController','renderRolesAdmin','',1),
    ( 8, '/manager/pages/','ManagerController','renderPageAdmin','',1),
    ( 9, '/manager/tests/','ManagerController','renderTestsAdmin','',1),
    (10, '/manager/logout/','ManagerController','render','logout',1);


