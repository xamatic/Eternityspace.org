<?php
$dbFile = dirname(__DIR__) . '/system/database.php';
if (!file_exists($dbFile)) {
    echo "DB_CONFIG_MISSING\n";
    exit(1);
}

require_once $dbFile;

$mysqli = @new mysqli(BOOM_DHOST, BOOM_DUSER, BOOM_DPASS, BOOM_DNAME);
if ($mysqli->connect_errno) {
    echo "DB_CONNECT_FAIL\n";
    exit(1);
}

$alters = array(
    'boom_users.user_pmusic' => "ALTER TABLE boom_users ADD COLUMN user_pmusic varchar(100) NOT NULL DEFAULT ''",
    'boom_users.pmusic' => "ALTER TABLE boom_users ADD COLUMN pmusic int(1) NOT NULL DEFAULT 0",
    'boom_setting.allow_pmusic' => "ALTER TABLE boom_setting ADD COLUMN allow_pmusic int(3) NOT NULL DEFAULT 100",
    'boom_setting.can_pmusic' => "ALTER TABLE boom_setting ADD COLUMN can_pmusic int(3) NOT NULL DEFAULT 100",
    'boom_setting.pmusic_gold_cost' => "ALTER TABLE boom_setting ADD COLUMN pmusic_gold_cost int(6) NOT NULL DEFAULT 50",
);

foreach ($alters as $key => $sql) {
    list($table, $column) = explode('.', $key);
    $check = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($check && $check->num_rows > 0) {
        echo "EXISTS:$key\n";
        continue;
    }
    if ($mysqli->query($sql)) {
        echo "ADDED:$key\n";
    } else {
        echo "FAILED:$key:" . $mysqli->error . "\n";
    }
}
