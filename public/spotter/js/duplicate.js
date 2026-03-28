window.SpotterDuplicate = {
  normalise(name) {
    name = name.toLowerCase().trim();
    name = name.replace(/[^a-z0-9\s]/g, '');
    ['pharmacies','pharmacy','chemists','chemist','pharmas','pharma','pharms','pharm'].forEach(s => {
      name = name.replace(new RegExp(`\\b${s}\\b`, 'g'), 'pharm');
    });
    return name.replace(/\s+/g, ' ').trim();
  },
  levenshtein(a, b) {
    const m = a.length, n = b.length;
    const dp = Array.from({length: m+1}, (_, i) =>
      Array.from({length: n+1}, (_, j) => i === 0 ? j : j === 0 ? i : 0)
    );
    for (let i = 1; i <= m; i++)
      for (let j = 1; j <= n; j++)
        dp[i][j] = a[i-1] === b[j-1] ? dp[i-1][j-1] : 1 + Math.min(dp[i-1][j], dp[i][j-1], dp[i-1][j-1]);
    return dp[m][n];
  },
  async check(pharmacyName, ward) {
    const norm = this.normalise(pharmacyName);
    const registry = await window.SpotterDB.getAllRegistry();
    for (const entry of registry) {
      const entryNorm = this.normalise(entry.pharmacy || '');
      if (entryNorm === norm) return { type: 'exact', match: entry.pharmacy };
      if (this.levenshtein(norm, entryNorm) <= 2 && entry.ward === ward) {
        return { type: 'fuzzy', match: entry.pharmacy };
      }
    }
    return null;
  }
};
