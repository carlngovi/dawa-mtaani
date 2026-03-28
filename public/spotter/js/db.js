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

function tx(storeName, mode = 'readonly') {
  return openDB().then(db => {
    const t = db.transaction(storeName, mode);
    const s = t.objectStore(storeName);
    return { t, s };
  });
}

function wrap(req) {
  return new Promise((res, rej) => {
    req.onsuccess = e => res(e.target.result);
    req.onerror = e => rej(e.target.error);
  });
}

window.SpotterDB = {
  // Device
  getDevice: async () => { const {s} = await tx('device'); return wrap(s.get(1)); },
  setDevice: async (data) => { const {s} = await tx('device','readwrite'); return wrap(s.put({id:1,...data})); },
  clearDevice: async () => { const {s} = await tx('device','readwrite'); return wrap(s.clear()); },

  // Submissions
  getAllSubmissions: async () => {
    const {s} = await tx('submissions');
    return wrap(s.getAll());
  },
  putSubmission: async (record) => { const {s} = await tx('submissions','readwrite'); return wrap(s.put(record)); },
  updateSubmission: async (localId, updates) => {
    const {s} = await tx('submissions','readwrite');
    return new Promise((res,rej) => {
      const getReq = s.get(localId);
      getReq.onsuccess = e => {
        const updated = {...e.target.result, ...updates};
        const putReq = s.put(updated);
        putReq.onsuccess = () => res(updated);
        putReq.onerror = e => rej(e.target.error);
      };
      getReq.onerror = e => rej(e.target.error);
    });
  },

  // Attendance
  getTodayAttendance: async () => {
    const {s} = await tx('attendance');
    const today = new Date().toISOString().slice(0,10);
    const idx = s.index('date');
    const all = await wrap(idx.getAll(today));
    return all.find(a => !a.clockOutAt) || null;
  },
  addAttendance: async (record) => { const {s} = await tx('attendance','readwrite'); return wrap(s.add(record)); },
  updateAttendance: async (id, updates) => {
    const {s} = await tx('attendance','readwrite');
    return new Promise((res,rej) => {
      const getReq = s.get(id);
      getReq.onsuccess = e => {
        const updated = {...e.target.result, ...updates};
        const putReq = s.put(updated);
        putReq.onsuccess = () => res(updated);
        putReq.onerror = e => rej(e.target.error);
      };
    });
  },

  // Follow-ups
  getAllFollowups: async () => { const {s} = await tx('followups'); return wrap(s.getAll()); },
  addFollowup: async (record) => { const {s} = await tx('followups','readwrite'); return wrap(s.add(record)); },
  updateFollowup: async (id, updates) => {
    const {s} = await tx('followups','readwrite');
    return new Promise((res,rej) => {
      const getReq = s.get(id);
      getReq.onsuccess = e => {
        const updated = {...e.target.result,...updates};
        const putReq = s.put(updated);
        putReq.onsuccess = () => res(updated);
        putReq.onerror = e => rej(e.target.error);
      };
    });
  },

  // Sync queue
  getSyncQueue: async () => { const {s} = await tx('syncQueue'); return wrap(s.getAll()); },
  addToQueue: async (item) => { const {s} = await tx('syncQueue','readwrite'); return wrap(s.add(item)); },
  removeFromQueue: async (id) => { const {s} = await tx('syncQueue','readwrite'); return wrap(s.delete(id)); },
  clearQueue: async () => { const {s} = await tx('syncQueue','readwrite'); return wrap(s.clear()); },
  countQueue: async () => { const {s} = await tx('syncQueue'); return wrap(s.count()); },

  // Registry
  getAllRegistry: async () => { const {s} = await tx('registry'); return wrap(s.getAll()); },
  clearAndReplaceRegistry: async (entries) => {
    const {t,s} = await tx('registry','readwrite');
    return new Promise((res,rej) => {
      const clearReq = s.clear();
      clearReq.onsuccess = () => {
        let remaining = entries.length;
        if (remaining === 0) { res(); return; }
        entries.forEach(entry => {
          const addReq = s.add(entry);
          addReq.onsuccess = () => { if (--remaining === 0) res(); };
          addReq.onerror = e => rej(e.target.error);
        });
      };
    });
  },

  // Settings
  getSetting: async (key) => { const {s} = await tx('settings'); const r = await wrap(s.get(key)); return r?.value; },
  setSetting: async (key, value) => { const {s} = await tx('settings','readwrite'); return wrap(s.put({key,value})); },

  // Nuclear clear
  clearAll: async () => {
    const db = await openDB();
    const stores = ['device','submissions','attendance','followups','syncQueue','registry','settings'];
    const t = db.transaction(stores,'readwrite');
    stores.forEach(name => t.objectStore(name).clear());
    return new Promise((res,rej) => { t.oncomplete = res; t.onerror = e => rej(e.target.error); });
  }
};
