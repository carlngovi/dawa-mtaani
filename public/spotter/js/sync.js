window.SpotterSync = {
  BACKOFF: [5000, 15000, 30000, 60000],

  async refreshToken() {
    const API = document.querySelector('meta[name=api-base]').content;
    const refreshToken = localStorage.getItem('dm_refresh_token');
    if (!refreshToken) return false;
    try {
      const res = await fetch(`${API}/auth/refresh`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ refresh_token: refreshToken })
      });
      if (!res.ok) return false;
      const data = await res.json();
      localStorage.setItem('dm_auth_token', data.token);
      localStorage.setItem('dm_refresh_token', data.refresh_token);
      const device = await window.SpotterDB.getDevice();
      if (device) {
        await window.SpotterDB.setDevice({ ...device, token: data.token, refreshToken: data.refresh_token });
      }
      return true;
    } catch {
      return false;
    }
  },

  async run(onProgress) {
    const queue = await window.SpotterDB.getSyncQueue();
    const pending = queue.filter(i => (i.attempts || 0) < 3);
    if (pending.length === 0) return { synced: 0, failed: 0, conflicts: 0, updatesReceived: 0 };

    const API = document.querySelector('meta[name=api-base]').content;
    // Read token from localStorage first, fall back to IndexedDB
    let token = localStorage.getItem('dm_auth_token');
    if (!token) {
      const device = await window.SpotterDB.getDevice();
      token = device?.token;
    }
    if (!token) return { synced: 0, failed: 0, conflicts: 0, updatesReceived: 0 };

    const headers = {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    };

    let synced = 0, failed = 0, conflicts = 0, updatesReceived = 0;

    const submissions = pending.filter(i => i.type === 'submission');
    const attendances = pending.filter(i => i.type === 'attendance');

    // Sync attendance records
    for (const item of attendances) {
      try {
        const endpoint = item.data.clockOutAt ? '/spotter/clock/out' : '/spotter/clock/in';
        const res = await fetch(`${API}${endpoint}`, {
          method: 'POST',
          headers,
          body: JSON.stringify({ lat: item.data.lat, lng: item.data.lng })
        });
        if (res.status === 401) {
          const refreshed = await this.refreshToken();
          if (refreshed) {
            headers['Authorization'] = `Bearer ${localStorage.getItem('dm_auth_token')}`;
            const retry = await fetch(`${API}${endpoint}`, {
              method: 'POST', headers,
              body: JSON.stringify({ lat: item.data.lat, lng: item.data.lng })
            });
            if (retry.ok || retry.status === 409) {
              await window.SpotterDB.removeFromQueue(item.id);
              synced++;
              continue;
            }
          } else {
            window.dispatchEvent(new Event('dm:session-expired'));
            return { synced, failed, conflicts, updatesReceived };
          }
        }
        if (res.ok || res.status === 409) {
          await window.SpotterDB.removeFromQueue(item.id);
          synced++;
        } else { failed++; }
      } catch { failed++; }
    }

    // Sync submissions in batch
    if (submissions.length > 0) {
      try {
        const allSubs = await window.SpotterDB.getAllSubmissions();
        const localIdMap = Object.fromEntries(allSubs.map(s => [s.localId, s]));
        const items = submissions.map(q => localIdMap[q.localId]).filter(Boolean);

        let res = await fetch(`${API}/spotter/sync`, {
          method: 'POST',
          headers,
          body: JSON.stringify({ items })
        });

        // Handle 401 with token refresh
        if (res.status === 401) {
          const refreshed = await this.refreshToken();
          if (refreshed) {
            headers['Authorization'] = `Bearer ${localStorage.getItem('dm_auth_token')}`;
            res = await fetch(`${API}/spotter/sync`, {
              method: 'POST', headers,
              body: JSON.stringify({ items })
            });
          } else {
            window.dispatchEvent(new Event('dm:session-expired'));
            return { synced, failed, conflicts, updatesReceived };
          }
        }

        if (res.ok) {
          const data = await res.json();
          for (const result of (data.results || [])) {
            const queueItem = submissions.find(q => q.localId === result.local_id);
            if (result.status === 'accepted') {
              await window.SpotterDB.updateSubmission(result.local_id, { syncStatus: 'synced', status: 'submitted' });
              if (queueItem) await window.SpotterDB.removeFromQueue(queueItem.id);
              synced++;
            } else if (result.status === 'conflict') {
              await window.SpotterDB.updateSubmission(result.local_id, { syncStatus: 'synced', status: 'held' });
              if (queueItem) await window.SpotterDB.removeFromQueue(queueItem.id);
              conflicts++;
            } else {
              if (queueItem) {
                if ((queueItem.attempts || 0) + 1 >= 3) {
                  await window.SpotterDB.updateSubmission(result.local_id, { syncStatus: 'failed' });
                  await window.SpotterDB.removeFromQueue(queueItem.id);
                }
              }
              failed++;
            }
          }
          // Count status updates received from server
          updatesReceived = (data.updates || []).length;
          for (const update of (data.updates || [])) {
            if (update.local_id && update.status) {
              await window.SpotterDB.updateSubmission(update.local_id, { status: update.status });
            }
          }
        }
      } catch { failed += submissions.length; }
    }

    const remaining = await window.SpotterDB.countQueue();
    if (onProgress) onProgress({ synced, failed, conflicts, remaining, updatesReceived });
    return { synced, failed, conflicts, updatesReceived };
  }
};
