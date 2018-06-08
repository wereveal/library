<?php
if (isset($argv[1])) {
    print 'The Password: ' . password_hash($argv[1], PASSWORD_DEFAULT) . "\n";
}
else {
    print 'SuperAdmin: ' . password_hash('letGSAin', PASSWORD_DEFAULT) . "\n";
    print 'Admin: ' . password_hash('letADMin', PASSWORD_DEFAULT) . "\n";
    print 'Manager: ' . password_hash('letMANin', PASSWORD_DEFAULT) . "\n";
}
?>
