<?php

require 'vendor/autoload.php';

function get($key) {
    if (array_key_exists($key, $_GET)) {
        return $_GET[$key];
    } else {
        return false;
    }
}
function formatSize($bytes) {
    if ($bytes < pow(1024, 1)) {
        return "$bytes b";
    } elseif ($bytes < pow(1024, 2)) {
        return round($bytes / pow(1024, 1), 2).' Kb';
    } elseif ($bytes < pow(1024, 3)) {
        return round($bytes / pow(1024, 2), 2).' Mb';
    } elseif ($bytes < pow(1024, 4)) {
        return round($bytes / pow(1024, 3), 2).' Gb';
    }
}

// Справка
// https://docs.mongodb.com/php-library/current/tutorial/crud/

// тут версия 1.4 поставить более новую нельзя пока расширение php старое!
// многих функций оттуда нет!

$uri = "mongodb://localhost:27017";
$uri = "mongodb://mongo:27017";

$client = new MongoDB\Client($uri);

// Операции
//$client->selectCollection();
//$client->selectDatabase();
//$database->getManager();
//$database->getDatabaseName();
//$database->drop();

// Операции с базой данных
//$database = $client->admin;
//$database->selectCollection('new');
//$database->getDatabaseName();
//$database->listCollections();
//$database->createCollection('new');
//$result = $database->modifyCollection('users', [
//    'keyPattern' => ['lastAccess' => 1],
//    'expireAfterSeconds' => 1000
//]);
//$cursor = $database->command(['ping' => 1]);
//var_dump($c->toArray()[0]);

// Методы коллекций
//$client->selectCollection('sample_analytics', 'customers');
//$intCount = $collection->countDocuments(['result.responseId' => ['$ne' => NULL]]);
//$result = $collection->find([ 'name' => 'Hinterland', 'brewery' => 'BrewDog' ] )->toArray();
//$document = $customers->findOne(['username' => 'wesley20']);
//foreach ($result as $entry) {
//    echo $entry['_id'], ': ', $entry['name'], "\n";
//}

//$collection->getTypeMap()
//[array] => MongoDB\Model\BSONArray
//[document] => MongoDB\Model\BSONDocument
//[root] => MongoDB\Model\BSONDocument

$output = [];


if (get('dropDb')) {
    $client->dropDatabase(get('dropDb'));
}
if (get('db') && get('dropCollection')) {
    $dbName = get('db');
    $db = $client->$dbName;
    $db->dropCollection(get('dropCollection'));
}
if (get('db') && get('add')) {
    $dbName = get('db');
    $db = $client->$dbName;
    $collectionExists = false;
    foreach ($db->listCollections() as $k => $v) {
        if ($v->getName() == get('add')) {
            $collectionExists = true;
        }
    }
    if (!$collectionExists) {
        $db->createCollection(get('add'));
    }
}
if (get('db') && get('renameCollection') && get('newname')) {
    $dbName = get('db');
    $collectionName = get('renameCollection');
    $collection = $client->$dbName->$collectionName;
    $collection->rename(get('newname'));
}
if (get('modifyCollection')) {
    //$collection->modifyCollection(get('сollection'));
}
if (get('db') && get('collection') && get('drop')) {
    $dbName = get('db');
    $collectionName = get('collection');
    $collection = $client->$dbName->$collectionName;
    $deleteResult = $collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId(get('drop'))]);
    //var_dump($deleteResult->getDeletedCount());
    //var_dump($deleteResult->isAcknowledged());
}

if (get('get')) {
    $dbs = $client->listDatabases();
    $data = [];
    foreach ($dbs as $k => $v) {
        $data []= [
            'name' => $v->getName(),
            'size' => $v->getSizeOnDisk(),
            'sizeFormatted' => formatSize($v->getSizeOnDisk()),
            'empty' => $v->isEmpty(),
        ];
    }
    $output ['databases']= $data;
    $output ['collections']= [];
    $dbName = get('db');
    if ($dbName) {
        $database = $client->$dbName;
        $collections = [];
        foreach ($database->listCollections() as $k => $v) {
            $collections []= [
                'name' => $v->getName(),
            ];
        }
        $output ['collections'] = $collections;
    }
}

if ($output) {
    exit(json_encode($output));
}



include "template.html";


if (!get('collection')) {
    return;
}
$dbName = get('db');
$collectionName = get('db');
$collectionName = get('collection');
$collection = $client->$dbName->$collectionName;
?>
    <h4>Коллекция <?=$collectionName?> [<?=$collection->countDocuments()?>]     <a href="?db=<?=$dbName?>&collection=<?=$collectionName?>&insert=1">insert</a></h4>
<?php


if ($collection->getCollectionName() == 'servers' && get('insert')) {
    $insertOneResult = $collection->insertOne($_SERVER);
    var_dump($insertOneResult->getInsertedCount());
    var_dump($insertOneResult->getInsertedId());
}

function printEntry($entry) {
    if (get_class($entry) == 'MongoDB\Model\BSONDocument') {
        foreach ($entry as $k => $v) {
            ?>
            <?=$k?> =
            <?php
            if (is_scalar($v) || $v instanceof Stringable) {
                echo $v;
            } else {
                if (get_class($v) == 'MongoDB\BSON\UTCDateTime') {
                    echo $v->toDateTime()->format('Y-m-d H:i:s');
                } elseif (get_class($v) == 'MongoDB\BSON\ObjectId') {
                    echo $v->getTimestamp().' ['.$v->__toString().'] ObjectId';
                } elseif (get_class($v) == 'MongoDB\BSON\Binary') {
                    echo 'binary [type='.$v->getType().']';
                } else if (get_class($v) == 'MongoDB\Model\BSONDocument') {
                    echo '<div style="padding: 10px; border: 1px solid #ccc">';
                    printEntry($v);
                    echo '</div>';
                } else {
                    var_dump(get_class($v));
                    echo '<pre>'.print_r($v, 1).'</pre>';
                }
            }
            echo ' <br />';
        }
    } else {
        var_dump($entry);
    }
}

//$result = $collection->find()->toArray();
//echo '<pre>'.print_r($result, 1).'</pre>';

$result = $collection->find();
foreach ($result as $entry) {
    $fields = [];
    foreach ($entry as $k => $v) {
        $fields [] = $k;
    }
    ?>
    <a href="?db=<?=$dbName?>&collection=<?=$collectionName?>&drop=<?=$entry->_id?>">удалить</a>
    <?php
    printEntry($entry);
    echo ' <hr />';
    /*echo '<pre>'.print_r($fields, 1).'</pre>';
    foreach ( as $k => $v) {

    }*/
}

