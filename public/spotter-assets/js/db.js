const DB_NAME = 'SpotterDB';
const DB_VERSION = 1;
let _db = null;

function openDB() {
  if (_db) return Promise.resolve(_db);
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(DB_NAME, DB_VERSION);
    req.onupgradeneeded = e => {
      const db = e.target.result;
      if (!db.objectStoreNames.contains('device')) {
        db.createObjectStore('device', { keyPath: 'id' });
      }
      if (!db.objectStoreNames.contains('submissions')) {
        const s = db.createObjectStore('submissions', { keyPath: 'localId' });
        s.createIndex('status', 'status');
        s.createIndex('date', 'date');
        s.createIndex('syncStatus', 'syncStatus');
      }
      if (!db.objectStoreNames.contains('attendance')) {
        const a = db.createObjectStore('attendance', { keyPath: 'id', autoIncrement: true });
        a.createIndex('date', 'date');
      }
      if (!db.objectStoreNames.contains('followups')) {
        const f = db.createObjectStore('followups', { keyPath: 'id', autoIncrement: true });
        f.createIndex('status', 'status');
        f.createIndex('submissionLocalId', 'submissionLocalId');
      }
      if (!db.objectStoreNames.contains('syncQueue')) {
        const q = db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
        q.createIndex('localId', 'localId');
        q.createIndex('type', 'type');
      }
      if (!db.objectStoreNames.contains('registry')) {
        const r = db.createObjectStore('registry', { keyPath: 'id', autoIncrement: true });
        r.createIndex('pharmacy', 'pharmacy');
        r.createIndex('ward', 'ward');
      }
      if (!db.objectStoreNames.contains('settings')) {
        db.createObjectStore('settings', { keyPath: 'key' });
      }
    };
    req.onsuccess = e => { _db = e.target.result; resolve(_db); };
    req.onerror = e => reject(e.target.error);
  });
}

function tx(storeName, mode) {
  mode = mode || 'readonly';
  return openDB().then(db => {
    const t = db.transaction(storeName, mode);
    const s = t.objectStore(storeName);
    return { t: t, s: s };
  });
}

function wrap(req) {
  return new Promise((res, rej) => {
    req.onsuccess = e => res(e.target.result);
    req.onerror = e => rej(e.target.error);
  });
}

window.SpotterDB = {
  getDevice: async function() { var r = await tx('device'); return wrap(r.s.get(1)); },
  setDevice: async function(data) { var r = await tx('device','readwrite'); return wrap(r.s.put(Object.assign({id:1}, data))); },
  clearDevice: async function() { var r = await tx('device','readwrite'); return wrap(r.s.clear()); },

  getAllSubmissions: async function() { var r = await tx('submissions'); return wrap(r.s.getAll()); },
  putSubmission: async function(record) { var r = await tx('submissions','readwrite'); return wrap(r.s.put(record)); },
  updateSubmission: async function(localId, updates) {
    var r = await tx('submissions','readwrite');
    return new Promise(function(res, rej) {
      var getReq = r.s.get(localId);
      getReq.onsuccess = function(e) {
        var updated = Object.assign({}, e.target.result, updates);
        var putReq = r.s.put(updated);
        putReq.onsuccess = function() { res(updated); };
        putReq.onerror = function(e2) { rej(e2.target.error); };
      };
      getReq.onerror = function(e) { rej(e.target.error); };
    });
  },

  getTodayAttendance: async function() {
    var r = await tx('attendance');
    var today = new Date().toISOString().slice(0,10);
    var idx = r.s.index('date');
    var all = await wrap(idx.getAll(today));
    return all.find(function(a) { return !a.clockOutAt; }) || null;
  },
  addAttendance: async function(record) { var r = await tx('attendance','readwrite'); return wrap(r.s.add(record)); },
  updateAttendance: async function(id, updates) {
    var r = await tx('attendance','readwrite');
    return new Promise(function(res, rej) {
      var getReq = r.s.get(id);
      getReq.onsuccess = function(e) {
        var updated = Object.assign({}, e.target.result, updates);
        var putReq = r.s.put(updated);
        putReq.onsuccess = function() { res(updated); };
        putReq.onerror = function(e2) { rej(e2.target.error); };
      };
    });
  },

  getAllFollowups: async function() { var r = await tx('followups'); return wrap(r.s.getAll()); },
  addFollowup: async function(record) { var r = await tx('followups','readwrite'); return wrap(r.s.add(record)); },
  updateFollowup: async function(id, updates) {
    var r = await tx('followups','readwrite');
    return new Promise(function(res, rej) {
      var getReq = r.s.get(id);
      getReq.onsuccess = function(e) {
        var updated = Object.assign({}, e.target.result, updates);
        var putReq = r.s.put(updated);
        putReq.onsuccess = function() { res(updated); };
        putReq.onerror = function(e2) { rej(e2.target.error); };
      };
    });
  },

  getSyncQueue: async function() { var r = await tx('syncQueue'); return wrap(r.s.getAll()); },
  addToQueue: async function(item) { var r = await tx('syncQueue','readwrite'); return wrap(r.s.add(item)); },
  removeFromQueue: async function(id) { var r = await tx('syncQueue','readwrite'); return wrap(r.s.delete(id)); },
  clearQueue: async function() { var r = await tx('syncQueue','readwrite'); return wrap(r.s.clear()); },
  countQueue: async function() { var r = await tx('syncQueue'); return wrap(r.s.count()); },

  getAllRegistry: async function() { var r = await tx('registry'); return wrap(r.s.getAll()); },
  clearAndReplaceRegistry: async function(entries) {
    var r = await tx('registry','readwrite');
    return new Promise(function(res, rej) {
      var clearReq = r.s.clear();
      clearReq.onsuccess = function() {
        var remaining = entries.length;
        if (remaining === 0) { res(); return; }
        entries.forEach(function(entry) {
          var addReq = r.s.add(entry);
          addReq.onsuccess = function() { if (--remaining === 0) res(); };
          addReq.onerror = function(e) { rej(e.target.error); };
        });
      };
    });
  },

  getSetting: async function(key) { var r = await tx('settings'); var v = await wrap(r.s.get(key)); return v ? v.value : undefined; },
  setSetting: async function(key, value) { var r = await tx('settings','readwrite'); return wrap(r.s.put({key:key,value:value})); },

  clearAll: async function() {
    var db = await openDB();
    var stores = ['device','submissions','attendance','followups','syncQueue','registry','settings'];
    var t = db.transaction(stores,'readwrite');
    stores.forEach(function(name) { t.objectStore(name).clear(); });
    return new Promise(function(res, rej) { t.oncomplete = res; t.onerror = function(e) { rej(e.target.error); }; });
  }
};
