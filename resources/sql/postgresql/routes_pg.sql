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
    ( 1, '/manager/','GuideManagerController','render','',1),
    ( 2, '/manager/login/','GuideManagerController','render','verifyLogin',1),
    ( 3, '/manager/routes/','GuideManagerController','renderRoutesAdmin','',1),
    ( 4, '/manager/constants/','GuideManagerController','renderConstantsAdmin','',1),
    ( 5, '/manager/people/','GuideManagerController','renderPeopleAdmin','',1),
    ( 6, '/manager/groups/','GuideManagerController','renderGroupsAdmin','',1),
    ( 7, '/manager/roles/','GuideManagerController','renderRolesAdmin','',1),
    ( 8, '/manager/pages/','GuideManagerController','renderPageAdmin','',1),
    ( 9, '/manager/tests/','GuideManagerController','renderTestsAdmin','',1),
    (10, '/manager/logout/','GuideManagerController','render','logout',1);


