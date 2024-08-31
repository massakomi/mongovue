<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MongoDB Manager</title>
</head>
<body>

<main id="app">

    <h1>
        Список баз данных
        <a href="#" @click="addDatabase">add</a>
    </h1>
    <ul>
        <li v-for="item of databases">
            <a href="#" @click.prevent="selectDatabase(item.name)">{{ item.name }}</a> &nbsp;
            <span style="color: #ccc; margin-left: 5px;">{{ item.sizeFormatted }}{{ item.isEmpty ? 'empty' : '' }}</span> &nbsp;
            <a href="#" @click.prevent="dropDatabase(item.name)">drop</a>
        </li>
    </ul>

    <template v-if="dbName">
        <h2>
            Коллекции базы данных {{dbName}}
            <a href="#" @click.prevent="addCollection">добавить</a>
        </h2>
        <ul>
            <li v-for="item of collections">
                <a :href="'?db='+dbName+'&collection='+item.name">{{item.name}}</a> &nbsp;
                <a href="#" @click.prevent="dropCollection(item.name)">drop</a> &nbsp;
                <a href="#" @click.prevent="renameCollection(item.name)">rename</a>
            </li>
        </ul>
    </template>


    <template v-if="collectionName">
        <h3>Документы коллекции {{collectionName}} [{{countDocuments}}] <a @click="insertDocument" href="#">insert</a></h3>
        <ul>
            <li v-for="(document, key) of documents">
                {{ document._id.value.$oid }} &nbsp;
                <a href="#" @click="dropDocument(document._id.value.$oid, key)">удалить</a> &nbsp;
                <a href="#" @click="toggleDocument(document._id.value.$oid)">view</a>
                <ul :hidden="documentId != document._id.value.$oid">
                    <li v-for="(value, field) in document">
                        <b>{{field}}</b> &nbsp;
                        <i>{{value.type}}</i> &nbsp;
                        <span>{{value.value}}</span>
                    </li>
                </ul>
            </li>
        </ul>
    </template>

</main>

<script type="module" src="js/script.js"></script>

</body>
</html>


