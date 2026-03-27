window.SpotterSync = {
  run: async function(onProgress) {
    var queue = await window.SpotterDB.getSyncQueue();
    var pending = queue.filter(function(i) { return (i.attempts || 0) < 3; });
    if (pending.length === 0) return { synced: 0, failed: 0, conflicts: 0 };

    var API = document.querySelector('meta[name=api-base]').content;
    var device = await window.SpotterDB.getDevice();
    if (!device || !device.token) return { synced: 0, failed: 0, conflicts: 0 };

    var headers = {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + device.token
    };

    var synced = 0, failed = 0, conflicts = 0;

    var submissions = pending.filter(function(i) { return i.type === 'submission'; });
    var attendances = pending.filter(function(i) { return i.type === 'attendance'; });

    // Sync attendance
    for (var a = 0; a < attendances.length; a++) {
      var item = attendances[a];
      try {
        var endpoint = item.data.clockOutAt ? '/spotter/clock/out' : '/spotter/clock/in';
        await fetch(API + endpoint, {
          method: 'POST',
          headers: headers,
          body: JSON.stringify({ lat: item.data.lat, lng: item.data.lng })
        });
        await window.SpotterDB.removeFromQueue(item.id);
        synced++;
      } catch(e) { failed++; }
    }

    // Sync submissions in batch
    if (submissions.length > 0) {
      try {
        var allSubs = await window.SpotterDB.getAllSubmissions();
        var localIdMap = {};
        allSubs.forEach(function(s) { localIdMap[s.localId] = s; });
        var items = submissions.map(function(q) { return localIdMap[q.localId]; }).filter(Boolean);

        var res = await fetch(API + '/spotter/sync', {
          method: 'POST',
          headers: headers,
          body: JSON.stringify({ items: items })
        });

        if (res.ok) {
          var data = await res.json();
          var results = data.results || [];
          for (var r = 0; r < results.length; r++) {
            var result = results[r];
            var queueItem = submissions.find(function(q) { return q.localId === result.local_id; });
            if (result.status === 'accepted') {
              await window.SpotterDB.updateSubmission(result.local_id, { syncStatus: 'synced', status: 'submitted' });
              if (queueItem) await window.SpotterDB.removeFromQueue(queueItem.id);
              synced++;
            } else if (result.status === 'conflict') {
              await window.SpotterDB.updateSubmission(result.local_id, { syncStatus: 'synced', status: 'held' });
              if (queueItem) await window.SpotterDB.removeFromQueue(queueItem.id);
              conflicts++;
            } else {
              if (queueItem && (queueItem.attempts || 0) + 1 >= 3) {
                await window.SpotterDB.updateSubmission(result.local_id, { syncStatus: 'failed' });
                await window.SpotterDB.removeFromQueue(queueItem.id);
              }
              failed++;
            }
          }
        }
      } catch(e) { failed += submissions.length; }
    }

    var remaining = await window.SpotterDB.countQueue();
    if (onProgress) onProgress({ synced: synced, failed: failed, conflicts: conflicts, remaining: remaining });
    return { synced: synced, failed: failed, conflicts: conflicts };
  }
};
