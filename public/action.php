<?php

use MongoDB\Client;

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

$env = parse_ini_file('.env');
$uri = $env['MONGO_URL'];

$client = new Client($uri);
$controller = new Controller($client);
$action = get('action');
$method = $action . 'Action';
if (method_exists($controller, $method)) {
    $controller->$method();
}

$output = [];
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
if (get('db') && get('collection') && get('insert')) {
    $dbName = get('db');
    $collectionName = get('collection');
    $collection = $client->$dbName->$collectionName;
    if ($collection->getCollectionName() == 'servers' && get('insert')) {
        $insertOneResult = $collection->insertOne($_SERVER);
        //var_dump($insertOneResult->getInsertedCount());
        //var_dump($insertOneResult->getInsertedId());
    }
}

function getDocumentField($value) {
    if (is_scalar($value) || $value instanceof Stringable) {
        $type = 'string';
    } else {
        $type = get_class($value);
        if (get_class($value) == 'MongoDB\BSON\UTCDateTime') {
            $value = $value->toDateTime()->format('Y-m-d H:i:s');
        } elseif (get_class($value) == 'MongoDB\BSON\ObjectId') {
            $value = $value->getTimestamp().' ['.$value->__toString().'] ObjectId';
        } elseif (get_class($value) == 'MongoDB\BSON\Binary') {
            $value = 'binary [type='.$value->getType().']';
        } else if (get_class($value) == 'MongoDB\Model\BSONDocument') {
            $value = getDocumentEntries($value);
        }
    }
    return [$value, $type];
}

function getDocumentEntries($document) {
    $fields = [];
    if (get_class($document) == 'MongoDB\Model\BSONDocument') {
        foreach ($document as $key => $value) {
            [$value, $type] = getDocumentField($value);
            $fields [$key]= [
                'type' => $type,
                'value' => $value,
            ];
            //$entries [$key]= $value;
        }
    } else {
        $fields []= [
            'type' => 'unknown',
            'value' => $document,
        ];
    }
    return $fields;
}

if (get('get')) {

    $dbs = $client->listDatabases();
    $output ['databases'] = [];
    foreach ($dbs as $k => $v) {
        $output ['databases'] []= [
            'name' => $v->getName(),
            'size' => $v->getSizeOnDisk(),
            'sizeFormatted' => formatSize($v->getSizeOnDisk()),
            'empty' => $v->isEmpty(),
        ];
    }

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

        $collectionName = get('collection');
        if ($collectionName) {
            $collection = $client->$dbName->$collectionName;
            $output ['countDocuments'] = $collection->countDocuments();

            $output ['documents'] = [];
            $documents = $collection->find();
            foreach ($documents as $document) {
                $entries = getDocumentEntries($document);
                $output ['documents'][]= $entries;
            }
        }
    }
}

if ($output) {
    header('Content-Type: application/json');
    exit(json_encode($output));
}