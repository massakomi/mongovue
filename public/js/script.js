
import { createApp } from './vendor/vue.esm-browser.js'


const Mongo = {
  data() {
    return {
      databases: [],
      dbName: false,
      collections: [],
      collectionName: false,
      documents: [],
      documentId: false,
    }
  },
  methods: {
    query: function (query='') {
      fetch(this.url() + query)
        .then(response => response.ok ? response.json() : Promise.reject(response))
        .then(json => {
          console.log(this.url() + query, json)
          Object.assign(this, json)
        });
    },
    url: function () {
      let url = 'action.php?get=1'
      if (this.dbName) {
        url += '&db=' + this.dbName
      }
      if (this.collectionName) {
        url += '&collection=' + this.collectionName
      }
      return url
    },
    renameCollection: function (name) {
      let newName = prompt('Новое имя коллекции', name);
      if (!newName || typeof newName == 'undefined') {
        return false;
      }
      this.collectionName = newName
      this.query(`&renameCollection=${name}&newname=${newName}`)
    },
    selectDatabase: function (name) {
      this.dbName = name
      this.collectionName = false
      this.documents = []
      history.pushState({}, '', `?db=${name}`);
      this.query()
    },
    dropDatabase: function (name) {
      this.query(`&action=dropDb&dropDb=${name}`)
    },
    addDatabase: function () {
      let newDb = prompt('Имя базы');
      if (!newDb || typeof newDb == 'undefined') {
        return false;
      }
      this.dbName = newDb
      this.addCollection()
    },
    dropCollection: function (name) {
      this.query(`&dropCollection=${name}`)
    },
    addCollection: function () {
      let collection = prompt('Имя коллекции', 'empty');
      if (!collection || typeof collection == 'undefined') {
        return false;
      }
      this.collectionName = collection
      this.query(`&add=${collection}`)
    },
    toggleDocument: function (id) {
      if (this.documentId === id) {
        this.documentId = false
      } else {
        this.documentId = id
      }
    },
    dropDocument: function (id) {
      this.query(`&drop=${id}`)
    },
    insertDocument: function () {
      this.query(`&insert=1`)
    },
    loadFromGet: function() {
      this.dbName = this.dbName || new URL(location.href).searchParams.get('db');
      if (!this.collectionName) {
        let collection = new URL(location.href).searchParams.get('collection');
        if (collection) {
          this.collectionName = collection;
        }
      }
    }
  },
  mounted() {
    this.loadFromGet()
    this.query()
  }
}

let app = createApp(Mongo)

app.mount('#app')
